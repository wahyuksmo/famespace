<?php

namespace Tests\Feature;

use App\Events\NoteEvent;
use App\Models\Family;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FamilyNotesTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $family;

    protected User $parent;

    protected User $child;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([NoteEvent::class]);

        $this->family = Family::create([
            'name' => 'Keluarga Catatan Test',
            'invite_code' => 'NOT-'.rand(1000, 9999),
            'monthly_budget' => 5000000,
            'currency' => 'IDR',
        ]);

        $this->parent = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ayah Note',
            'email' => 'ayah.note.'.rand(1000, 9999).'@test.com',
            'password' => bcrypt('password123'),
            'role' => 'Ayah',
        ]);

        $this->child = User::create([
            'family_id' => $this->family->id,
            'name' => 'Anak Note',
            'email' => 'anak.note.'.rand(1000, 9999).'@test.com',
            'password' => bcrypt('password123'),
            'role' => 'Anak',
        ]);
    }

    public function test_authenticated_user_can_view_notes_index(): void
    {
        $response = $this->actingAs($this->parent)->get(route('notes.index'));

        $response->assertOk()
            ->assertViewIs('modules.note.index')
            ->assertSee('Papan Catatan');
    }

    public function test_family_member_can_create_sticky_note(): void
    {
        $payload = [
            'title' => 'Password WiFi Baru',
            'content' => 'SSID: RumahHarmoni, Pass: Berkah12345',
            'color' => 'yellow',
            'is_pinned' => 1,
        ];

        $response = $this->actingAs($this->child)->post(route('notes.store'), $payload);

        $response->assertRedirect(route('notes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('t_notes', [
            'family_id' => $this->family->id,
            'user_id' => $this->child->id,
            'title' => 'Password WiFi Baru',
            'is_pinned' => 1,
        ]);

        Event::assertDispatched(NoteEvent::class);
    }

    public function test_parent_and_author_can_toggle_pin(): void
    {
        $note = Note::create([
            'family_id' => $this->family->id,
            'user_id' => $this->child->id,
            'title' => 'Daftar Belanja',
            'content' => 'Beras, Telur, Susu',
            'color' => 'mint',
            'is_pinned' => false,
        ]);

        // Parent toggles pin
        $response = $this->actingAs($this->parent)->patch(route('notes.toggle_pin', $note->id));
        $response->assertSessionHas('success');

        $this->assertTrue($note->fresh()->is_pinned);
    }

    public function test_parent_and_author_can_update_note(): void
    {
        $note = Note::create([
            'family_id' => $this->family->id,
            'user_id' => $this->child->id,
            'title' => 'Jadwal Les',
            'content' => 'Hari Rabu jam 3 sore',
            'color' => 'blue',
            'is_pinned' => false,
        ]);

        $updatePayload = [
            'title' => 'Jadwal Les Diganti',
            'content' => 'Hari Kamis jam 4 sore',
            'color' => 'pink',
        ];

        $response = $this->actingAs($this->child)->put(route('notes.update', $note->id), $updatePayload);

        $response->assertRedirect(route('notes.index'));
        $this->assertDatabaseHas('t_notes', [
            'id' => $note->id,
            'title' => 'Jadwal Les Diganti',
            'color' => 'pink',
        ]);
    }

    public function test_child_cannot_update_or_delete_other_members_note(): void
    {
        $parentNote = Note::create([
            'family_id' => $this->family->id,
            'user_id' => $this->parent->id,
            'title' => 'Aturan Rumah',
            'content' => 'Tidak boleh main game lewat jam 9 malam',
            'color' => 'orange',
            'is_pinned' => true,
        ]);

        // Child tries to edit parent's note
        $response = $this->actingAs($this->child)->put(route('notes.update', $parentNote->id), [
            'title' => 'Aturan Baru',
            'content' => 'Bebas main game',
        ]);
        $response->assertForbidden();

        // Child tries to delete parent's note
        $deleteResponse = $this->actingAs($this->child)->delete(route('notes.destroy', $parentNote->id));
        $deleteResponse->assertForbidden();

        $this->assertDatabaseHas('t_notes', ['id' => $parentNote->id]);
    }

    public function test_parent_can_delete_note(): void
    {
        $note = Note::create([
            'family_id' => $this->family->id,
            'user_id' => $this->child->id,
            'title' => 'Catatan Lama',
            'content' => 'Sudah tidak relevan',
            'color' => 'yellow',
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($this->parent)->delete(route('notes.destroy', $note->id));
        $response->assertRedirect(route('notes.index'));

        $this->assertDatabaseMissing('t_notes', ['id' => $note->id]);
    }

    public function test_pinned_note_appears_on_dashboard(): void
    {
        Note::create([
            'family_id' => $this->family->id,
            'user_id' => $this->parent->id,
            'title' => 'Catatan Kulkas Pinned',
            'content' => 'Pesan penting di beranda',
            'color' => 'yellow',
            'is_pinned' => true,
        ]);

        $response = $this->actingAs($this->parent)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Papan Kulkas Keluarga')
            ->assertSee('Catatan Kulkas Pinned');
    }
}
