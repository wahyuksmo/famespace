<?php

namespace Tests\Feature;

use App\Events\FamilyMemberEvent;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JoinFamilyTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $newFamily;

    protected User $kickedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->newFamily = Family::create([
            'name' => 'Keluarga Bahagia',
            'invite_code' => 'KODE-BAHAGIA',
            'monthly_budget' => 3000000,
            'currency' => 'IDR',
        ]);

        $this->kickedUser = User::create([
            'family_id' => null,
            'name' => 'User Ter-Kick',
            'email' => 'kicked@test.com',
            'password' => Hash::make('password123'),
            'role' => 'Anak',
        ]);
    }

    public function test_join_family_page_is_accessible(): void
    {
        $response = $this->get(route('join.family'));
        $response->assertStatus(200);
        $response->assertSee('Gabung dengan Kode');
        $response->assertSee('Kode Undangan Keluarga');
    }

    public function test_kicked_user_can_join_new_family_successfully(): void
    {
        Event::fake([FamilyMemberEvent::class]);

        $response = $this->post(route('join.family.process'), [
            'invite_code' => 'KODE-BAHAGIA',
            'email' => 'kicked@test.com',
            'password' => 'password123',
            'role' => 'Ibu',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->kickedUser);

        $this->kickedUser->refresh();
        $this->assertEquals($this->newFamily->id, $this->kickedUser->family_id);
        $this->assertEquals('Ibu', $this->kickedUser->role);

        Event::assertDispatched(FamilyMemberEvent::class, function ($event) {
            return $event->action === 'member_joined'
                && $event->userId === $this->kickedUser->id
                && (string) $event->familyId === (string) $this->newFamily->id;
        });
    }

    public function test_cannot_join_with_invalid_invite_code(): void
    {
        $response = $this->post(route('join.family.process'), [
            'invite_code' => 'INVALID-CODE',
            'email' => 'kicked@test.com',
            'password' => 'password123',
            'role' => 'Anak',
        ]);

        $response->assertSessionHasErrors('invite_code');
        $this->assertGuest();
        $this->assertNull($this->kickedUser->fresh()->family_id);
    }

    public function test_cannot_join_with_wrong_password(): void
    {
        $response = $this->post(route('join.family.process'), [
            'invite_code' => 'KODE-BAHAGIA',
            'email' => 'kicked@test.com',
            'password' => 'salah-password',
            'role' => 'Anak',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertNull($this->kickedUser->fresh()->family_id);
    }

    public function test_active_user_with_existing_family_cannot_join(): void
    {
        $oldFamily = Family::create([
            'name' => 'Keluarga Lama',
            'invite_code' => 'KODE-LAMA',
            'monthly_budget' => 1000000,
            'currency' => 'IDR',
        ]);

        $activeUser = User::create([
            'family_id' => $oldFamily->id,
            'name' => 'User Masih Ada Keluarga',
            'email' => 'active@test.com',
            'password' => Hash::make('password123'),
            'role' => 'Anak',
        ]);

        $response = $this->post(route('join.family.process'), [
            'invite_code' => 'KODE-BAHAGIA',
            'email' => 'active@test.com',
            'password' => 'password123',
            'role' => 'Anak',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertEquals($oldFamily->id, $activeUser->fresh()->family_id);
    }
}
