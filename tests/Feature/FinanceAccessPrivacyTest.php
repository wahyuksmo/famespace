<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Family;
use App\Models\Finance;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FinanceAccessPrivacyTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $family;

    protected User $ayah;

    protected User $anak;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Bahagia',
            'invite_code' => 'FAM-TEST',
            'monthly_budget' => 5000000,
            'currency' => 'IDR',
        ]);

        $this->ayah = User::create([
            'family_id' => $this->family->id,
            'name' => 'Budi Ayah',
            'email' => 'ayah@test.com',
            'password' => bcrypt('password'),
            'role' => 'Ayah',
            'can_view_all_finances' => false, // Ayah role automatically has full view
        ]);

        $this->anak = User::create([
            'family_id' => $this->family->id,
            'name' => 'Andi Anak',
            'email' => 'anak@test.com',
            'password' => bcrypt('password'),
            'role' => 'Anak',
            'can_view_all_finances' => false,
        ]);

        $this->category = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Jajan',
            'type' => 'expense',
            'icon' => '🍔',
            'color' => '#E07A5F',
        ]);
    }

    public function test_ayah_can_toggle_member_finance_access(): void
    {
        $response = $this->actingAs($this->ayah)->patchJson(route('members.toggle_finance', $this->anak->id), [
            'can_view_all_finances' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'can_view_all_finances' => true,
        ]);

        $this->assertTrue($this->anak->fresh()->can_view_all_finances);
    }

    public function test_non_ayah_cannot_toggle_finance_access(): void
    {
        $response = $this->actingAs($this->anak)->patchJson(route('members.toggle_finance', $this->ayah->id), [
            'can_view_all_finances' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_restricted_member_only_sees_own_transactions_server_side(): void
    {
        // Transaksi Ayah
        Finance::create([
            'family_id' => $this->family->id,
            'user_id' => $this->ayah->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 500000,
            'description' => 'Rahasia Belanja Ayah',
            'transaction_date' => now()->toDateString(),
        ]);

        // Transaksi Anak
        Finance::create([
            'family_id' => $this->family->id,
            'user_id' => $this->anak->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 25000,
            'description' => 'Beli Es Krim Anak',
            'transaction_date' => now()->toDateString(),
        ]);

        // Anak (restricted) view /finances
        $responseAnak = $this->actingAs($this->anak)->get(route('finances.index'));
        $responseAnak->assertStatus(200);
        $responseAnak->assertSee('Beli Es Krim Anak');
        $responseAnak->assertDontSee('Rahasia Belanja Ayah');

        // Anak DataTables endpoint
        $dtAnak = $this->actingAs($this->anak)->getJson(route('finances.paginate', ['draw' => 1, 'start' => 0, 'length' => 10]));
        $dtAnak->assertStatus(200);
        $dataAnak = $dtAnak->json('data');
        $this->assertCount(1, $dataAnak);
        $this->assertEquals('Beli Es Krim Anak', $dataAnak[0]['description']);

        // Ayah view /finances can see both
        $responseAyah = $this->actingAs($this->ayah)->get(route('finances.index'));
        $responseAyah->assertStatus(200);
        $responseAyah->assertSee('Beli Es Krim Anak');
        $responseAyah->assertSee('Rahasia Belanja Ayah');
    }

    public function test_restricted_member_cannot_modify_or_delete_others_transaction(): void
    {
        $trxAyah = Finance::create([
            'family_id' => $this->family->id,
            'user_id' => $this->ayah->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 750000,
            'description' => 'Cicilan Gadget',
            'transaction_date' => now()->toDateString(),
        ]);

        // Attempt update by anak
        $updateResp = $this->actingAs($this->anak)->putJson(route('finances.update', $trxAyah->id), [
            'type' => 'expense',
            'amount' => 10000,
            'description' => 'Hacked',
            'transaction_date' => now()->toDateString(),
        ]);
        $updateResp->assertStatus(422);

        // Attempt delete by anak
        $deleteResp = $this->actingAs($this->anak)->deleteJson(route('finances.destroy', $trxAyah->id));
        $deleteResp->assertStatus(422);

        // Verify still in database
        $this->assertDatabaseHas('t_finances', [
            'id' => $trxAyah->id,
            'description' => 'Cicilan Gadget',
        ]);
    }
}
