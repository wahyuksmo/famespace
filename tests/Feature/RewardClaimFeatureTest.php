<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RewardClaimFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $family;

    protected User $ayah;

    protected User $anak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Bahagia',
            'invite_code' => 'REWARD123',
            'monthly_budget' => 5000000,
            'currency' => 'IDR',
        ]);

        $this->ayah = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ayah Rudi',
            'email' => 'ayah.rudi@test.com',
            'password' => bcrypt('password'),
            'role' => 'Ayah',
            'points' => 100,
        ]);

        $this->anak = User::create([
            'family_id' => $this->family->id,
            'name' => 'Anak Doni',
            'email' => 'anak.doni@test.com',
            'password' => bcrypt('password'),
            'role' => 'Anak',
            'points' => 50,
        ]);
    }

    public function test_parent_can_create_reward_in_catalog(): void
    {
        $response = $this->actingAs($this->ayah)->postJson(route('rewards.store'), [
            'title' => 'Main Game 1 Jam',
            'description' => 'Boleh main PS setelah PR selesai',
            'points' => 20,
            'icon' => '🎮',
            'stock' => 5,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Main Game 1 Jam',
                    'points' => 20,
                    'stock' => 5,
                ],
            ]);

        $this->assertDatabaseHas('m_rewards', [
            'family_id' => $this->family->id,
            'title' => 'Main Game 1 Jam',
            'points' => 20,
            'stock' => 5,
        ]);
    }

    public function test_non_parent_cannot_create_reward(): void
    {
        $response = $this->actingAs($this->anak)->postJson(route('rewards.store'), [
            'title' => 'Beli Sepeda',
            'points' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_member_can_claim_reward_when_points_are_sufficient(): void
    {
        $reward = Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Es Krim Coklat',
            'points' => 30,
            'icon' => '🍦',
            'stock' => 3,
            'created_by_user_id' => $this->ayah->id,
        ]);

        $this->assertEquals(50, $this->anak->fresh()->points);

        $response = $this->actingAs($this->anak)->postJson(route('rewards.claim', $reward->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'user_points' => 20, // 50 - 30
            ]);

        // Points deducted
        $this->assertEquals(20, $this->anak->fresh()->points);

        // Stock decremented
        $this->assertEquals(2, $reward->fresh()->stock);

        // Pending claim created
        $this->assertDatabaseHas('t_reward_claims', [
            'family_id' => $this->family->id,
            'reward_id' => $reward->id,
            'user_id' => $this->anak->id,
            'points_spent' => 30,
            'status' => 'pending',
        ]);
    }

    public function test_member_cannot_claim_if_points_insufficient(): void
    {
        $reward = Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Liburan ke Bali',
            'points' => 200, // Anak only has 50 points
            'icon' => '🏖️',
            'stock' => 1,
            'created_by_user_id' => $this->ayah->id,
        ]);

        $response = $this->actingAs($this->anak)->postJson(route('rewards.claim', $reward->id));

        $response->assertStatus(422);
        $this->assertEquals(50, $this->anak->fresh()->points);
        $this->assertEquals(1, $reward->fresh()->stock);
    }

    public function test_member_cannot_claim_if_stock_out(): void
    {
        $reward = Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Tiket Bioskop',
            'points' => 20,
            'icon' => '🎬',
            'stock' => 0, // Out of stock
            'created_by_user_id' => $this->ayah->id,
        ]);

        $response = $this->actingAs($this->anak)->postJson(route('rewards.claim', $reward->id));

        $response->assertStatus(422);
        $this->assertEquals(50, $this->anak->fresh()->points);
    }

    public function test_parent_can_approve_claim(): void
    {
        $reward = Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Buku Komik',
            'points' => 25,
            'icon' => '📚',
            'stock' => 2,
            'created_by_user_id' => $this->ayah->id,
        ]);

        $claim = RewardClaim::create([
            'family_id' => $this->family->id,
            'reward_id' => $reward->id,
            'user_id' => $this->anak->id,
            'points_spent' => 25,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->ayah)->patchJson(route('rewards.claims.approve', $claim->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $claim->refresh();
        $this->assertEquals('approved', $claim->status);
        $this->assertEquals($this->ayah->id, $claim->approved_by_user_id);
        $this->assertNotNull($claim->approved_at);
    }

    public function test_parent_can_reject_claim_which_refunds_points_and_restores_stock(): void
    {
        $reward = Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Uang Saku Tambahan',
            'points' => 30,
            'icon' => '💵',
            'stock' => 4,
            'created_by_user_id' => $this->ayah->id,
        ]);

        // Simulasikan klaim (poin anak terpotong jadi 20, stok jadi 4)
        $this->anak->points = 20;
        $this->anak->save();

        $claim = RewardClaim::create([
            'family_id' => $this->family->id,
            'reward_id' => $reward->id,
            'user_id' => $this->anak->id,
            'points_spent' => 30,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->ayah)->patchJson(route('rewards.claims.reject', $claim->id), [
            'notes' => 'Tugas belum tuntas semuanya.',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $claim->refresh();
        $this->assertEquals('rejected', $claim->status);
        $this->assertEquals('Tugas belum tuntas semuanya.', $claim->notes);

        // Poin dikembalikan ke anak: 20 + 30 = 50
        $this->assertEquals(50, $this->anak->fresh()->points);

        // Stok bertambah kembali: 4 + 1 = 5
        $this->assertEquals(5, $reward->fresh()->stock);
    }

    public function test_non_parent_cannot_approve_or_reject_claim(): void
    {
        $reward = Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Hadiah Test',
            'points' => 10,
            'stock' => 1,
            'created_by_user_id' => $this->ayah->id,
        ]);

        $claim = RewardClaim::create([
            'family_id' => $this->family->id,
            'reward_id' => $reward->id,
            'user_id' => $this->anak->id,
            'points_spent' => 10,
            'status' => 'pending',
        ]);

        $approveRes = $this->actingAs($this->anak)->patchJson(route('rewards.claims.approve', $claim->id));
        $approveRes->assertStatus(403);

        $rejectRes = $this->actingAs($this->anak)->patchJson(route('rewards.claims.reject', $claim->id));
        $rejectRes->assertStatus(403);
    }

    public function test_can_fetch_serverside_paginated_rewards(): void
    {
        Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Hadiah Satu',
            'points' => 10,
            'created_by_user_id' => $this->ayah->id,
        ]);

        Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Hadiah Dua',
            'points' => 20,
            'created_by_user_id' => $this->ayah->id,
        ]);

        $response = $this->actingAs($this->ayah)->getJson(route('rewards.paginate', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data' => [
                    '*' => ['id', 'title', 'points', 'icon', 'has_stock', 'stock_text', 'can_afford', 'points_needed'],
                ],
                'current_page',
                'total',
            ]);

        $this->assertEquals(2, $response->json('recordsTotal'));
    }

    public function test_serverside_reward_search_filters_results(): void
    {
        Reward::create([
            'family_id' => $this->family->id,
            'title' => 'Tiket Bioskop',
            'points' => 15,
            'created_by_user_id' => $this->ayah->id,
        ]);

        Reward::create([
            'family_id' => $this->family->id,
            'title' => 'PlayStation 1 Jam',
            'points' => 25,
            'created_by_user_id' => $this->ayah->id,
        ]);

        $response = $this->actingAs($this->ayah)->getJson(route('rewards.paginate', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Bioskop'],
        ]));

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));
        $this->assertEquals('Tiket Bioskop', $response->json('data.0.title'));
    }
}
