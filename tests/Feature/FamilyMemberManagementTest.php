<?php

namespace Tests\Feature;

use App\Events\FamilyMemberEvent;
use App\Models\Chore;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FamilyMemberManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $family;

    protected User $ayah;

    protected User $anak;

    protected User $intruder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Cemara',
            'invite_code' => 'FAM-SAFE',
            'monthly_budget' => 5000000,
            'currency' => 'IDR',
        ]);

        $this->ayah = User::create([
            'family_id' => $this->family->id,
            'name' => 'Budi Santoso',
            'email' => 'ayah.budi@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Ayah',
            'can_view_all_finances' => false,
        ]);

        $this->anak = User::create([
            'family_id' => $this->family->id,
            'name' => 'Citra Santoso',
            'email' => 'citra@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Anak',
            'can_view_all_finances' => false,
        ]);

        $this->intruder = User::create([
            'family_id' => $this->family->id,
            'name' => 'Penyusup Liar',
            'email' => 'penyusup@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Lainnya',
            'can_view_all_finances' => false,
        ]);
    }

    public function test_ayah_can_kick_intruder_or_member_successfully(): void
    {
        Event::fake([FamilyMemberEvent::class]);

        // Berikan chore untuk si penyusup
        $chore = Chore::create([
            'family_id' => $this->family->id,
            'assigned_to_user_id' => $this->intruder->id,
            'title' => 'Tugas Cuci Piring',
            'points' => 10,
            'status' => 'pending',
        ]);

        // Simulasikan session user di DB
        DB::table('sessions')->insert([
            'id' => 'sess_intruder_123',
            'user_id' => $this->intruder->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Testing Browser',
            'payload' => 'dummy_payload',
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($this->ayah)->deleteJson(route('members.kick', $this->intruder->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'kicked_user_id' => $this->intruder->id,
        ]);

        // Verifikasi intruder tidak lagi memiliki family_id
        $this->intruder->refresh();
        $this->assertNull($this->intruder->family_id);
        $this->assertFalse((bool) $this->intruder->can_view_all_finances);

        // Verifikasi chore dilepas dari penugasan
        $chore->refresh();
        $this->assertNull($chore->assigned_to_user_id);

        // Verifikasi sesi aktif intruder di DB terhapus
        $this->assertDatabaseMissing('sessions', [
            'user_id' => $this->intruder->id,
        ]);

        // Verifikasi event FamilyMemberEvent dibroadcast
        Event::assertDispatched(FamilyMemberEvent::class, function ($event) {
            return $event->action === 'member_kicked'
                && $event->data['kicked_user_id'] === $this->intruder->id;
        });
    }

    public function test_ayah_cannot_kick_self(): void
    {
        $response = $this->actingAs($this->ayah)->deleteJson(route('members.kick', $this->ayah->id));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Anda tidak dapat mengeluarkan akun Anda sendiri dari keluarga.',
        ]);

        $this->ayah->refresh();
        $this->assertEquals($this->family->id, $this->ayah->family_id);
    }

    public function test_cannot_kick_another_ayah(): void
    {
        $anotherAyah = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ayah Kedua',
            'email' => 'ayah2@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Ayah',
        ]);

        $response = $this->actingAs($this->ayah)->deleteJson(route('members.kick', $anotherAyah->id));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Tidak dapat mengeluarkan akun Orang Tua (Admin Utama).',
        ]);

        $anotherAyah->refresh();
        $this->assertEquals($this->family->id, $anotherAyah->family_id);
    }

    public function test_cannot_kick_member_from_another_family(): void
    {
        $otherFamily = Family::create([
            'name' => 'Keluarga Lain',
            'invite_code' => 'FAM-OTHR',
            'monthly_budget' => 3000000,
            'currency' => 'IDR',
        ]);

        $stranger = User::create([
            'family_id' => $otherFamily->id,
            'name' => 'Orang Lain',
            'email' => 'stranger@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Anak',
        ]);

        $response = $this->actingAs($this->ayah)->deleteJson(route('members.kick', $stranger->id));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Anggota keluarga tidak ditemukan.',
        ]);

        $stranger->refresh();
        $this->assertEquals($otherFamily->id, $stranger->family_id);
    }

    public function test_non_ayah_cannot_kick_member(): void
    {
        $response = $this->actingAs($this->anak)->deleteJson(route('members.kick', $this->intruder->id));

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Hanya Orang Tua (Admin Keluarga) yang berhak mengeluarkan anggota keluarga.',
        ]);

        $this->intruder->refresh();
        $this->assertEquals($this->family->id, $this->intruder->family_id);
    }

    public function test_ayah_can_regenerate_invite_code(): void
    {
        $oldCode = $this->family->invite_code;

        $response = $this->actingAs($this->ayah)->postJson(route('members.regenerate_invite'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->family->refresh();
        $this->assertNotEquals($oldCode, $this->family->invite_code);
        $this->assertStringStartsWith('FAM-', $this->family->invite_code);
    }

    public function test_non_ayah_cannot_regenerate_invite_code(): void
    {
        $response = $this->actingAs($this->anak)->postJson(route('members.regenerate_invite'));

        $response->assertStatus(403);
    }

    public function test_kicked_user_cannot_access_dashboard(): void
    {
        // Kicked user has family_id = null
        $this->intruder->update(['family_id' => null]);

        $response = $this->actingAs($this->intruder)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_kicked_user_cannot_login(): void
    {
        $this->intruder->update(['family_id' => null]);

        $response = $this->postJson(route('login'), [
            'email' => 'penyusup@test.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Akun Anda tidak lagi terdaftar dalam ruang keluarga manapun.',
        ]);
        $this->assertGuest();
    }

    public function test_ayah_can_update_member_password(): void
    {
        $response = $this->actingAs($this->ayah)->patchJson(route('members.update_password', $this->anak->id), [
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->anak->refresh();
        $this->assertTrue(Hash::check('newsecret123', $this->anak->password));
    }

    public function test_member_can_update_own_password(): void
    {
        $response = $this->actingAs($this->anak)->patchJson(route('members.update_password', $this->anak->id), [
            'password' => 'anakpass123',
            'password_confirmation' => 'anakpass123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->anak->refresh();
        $this->assertTrue(Hash::check('anakpass123', $this->anak->password));
    }

    public function test_member_cannot_update_another_member_password(): void
    {
        $response = $this->actingAs($this->anak)->patchJson(route('members.update_password', $this->intruder->id), [
            'password' => 'hacked123',
            'password_confirmation' => 'hacked123',
        ]);

        $response->assertStatus(403);
    }

    public function test_password_update_requires_confirmation_and_min_length(): void
    {
        $response = $this->actingAs($this->ayah)->patchJson(route('members.update_password', $this->anak->id), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($this->ayah)->patchJson(route('members.update_password', $this->anak->id), [
            'password' => 'mismatch1',
            'password_confirmation' => 'mismatch2',
        ]);

        $response->assertStatus(422);
    }

    public function test_ayah_can_update_member_role(): void
    {
        Event::fake([FamilyMemberEvent::class]);

        $response = $this->actingAs($this->ayah)->patchJson(route('members.update_role', $this->anak->id), [
            'role' => 'Ibu',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'role' => 'Ibu',
        ]);

        $this->anak->refresh();
        $this->assertEquals('Ibu', $this->anak->role);
    }

    public function test_non_ayah_cannot_update_member_role(): void
    {
        $response = $this->actingAs($this->anak)->patchJson(route('members.update_role', $this->intruder->id), [
            'role' => 'Ibu',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_update_role_of_ayah(): void
    {
        $response = $this->actingAs($this->ayah)->patchJson(route('members.update_role', $this->ayah->id), [
            'role' => 'Anak',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Peran akun Orang Tua (Admin Utama) tidak dapat diubah.',
        ]);
    }

    public function test_role_update_validates_allowed_roles(): void
    {
        $response = $this->actingAs($this->ayah)->patchJson(route('members.update_role', $this->anak->id), [
            'role' => 'SuperadminHacker',
        ]);

        $response->assertStatus(422);
    }

    public function test_ibu_can_manage_members_and_regenerate_invite_code(): void
    {
        $ibu = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ibu Siti',
            'email' => 'ibu.siti@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Ibu',
        ]);

        $targetAnak = User::create([
            'family_id' => $this->family->id,
            'name' => 'Anak Dua',
            'email' => 'anak2@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Anak',
        ]);

        // Ibu can update role of Anak
        $roleResp = $this->actingAs($ibu)->patchJson(route('members.update_role', $targetAnak->id), [
            'role' => 'ART',
        ]);
        $roleResp->assertStatus(200);
        $this->assertEquals('ART', $targetAnak->fresh()->role);

        // Ibu can toggle finance access of non-parent
        $finResp = $this->actingAs($ibu)->patchJson(route('members.toggle_finance', $targetAnak->id), [
            'can_view_all_finances' => true,
        ]);
        $finResp->assertStatus(200);
        $this->assertTrue($targetAnak->fresh()->can_view_all_finances);

        // Ibu can regenerate invite code
        $regenResp = $this->actingAs($ibu)->postJson(route('members.regenerate_invite'));
        $regenResp->assertStatus(200);

        // Ibu can kick non-parent member
        $kickResp = $this->actingAs($ibu)->deleteJson(route('members.kick', $targetAnak->id));
        $kickResp->assertStatus(200);
        $this->assertNull($targetAnak->fresh()->family_id);
    }

    public function test_ibu_and_ayah_are_mutually_protected(): void
    {
        $ibu = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ibu Siti',
            'email' => 'ibu.siti@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Ibu',
        ]);

        // Ibu cannot kick Ayah
        $respKickAyah = $this->actingAs($ibu)->deleteJson(route('members.kick', $this->ayah->id));
        $respKickAyah->assertStatus(422);

        // Ayah cannot kick Ibu
        $respKickIbu = $this->actingAs($this->ayah)->deleteJson(route('members.kick', $ibu->id));
        $respKickIbu->assertStatus(422);

        // Ibu cannot demote Ayah
        $respDemoteAyah = $this->actingAs($ibu)->patchJson(route('members.update_role', $this->ayah->id), [
            'role' => 'Anak',
        ]);
        $respDemoteAyah->assertStatus(422);

        // Ayah cannot demote Ibu
        $respDemoteIbu = $this->actingAs($this->ayah)->patchJson(route('members.update_role', $ibu->id), [
            'role' => 'Anak',
        ]);
        $respDemoteIbu->assertStatus(422);
    }
}
