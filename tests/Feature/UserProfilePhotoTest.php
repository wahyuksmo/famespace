<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfilePhotoTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $family;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Bahagia',
            'invite_code' => 'FAM-TEST',
            'monthly_budget' => 5000000,
            'currency' => 'IDR',
        ]);

        $this->user = User::create([
            'family_id' => $this->family->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'Ayah',
        ]);
    }

    public function test_user_can_upload_profile_photo_and_update_name(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('my-profile.png', 400, 400);

        $response = $this->actingAs($this->user)
            ->postJson(route('profile.update'), [
                'name' => 'Budi Pratama',
                'avatar' => $file,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $this->user->id,
                    'name' => 'Budi Pratama',
                ],
            ]);

        $this->user->refresh();
        $this->assertSame('Budi Pratama', $this->user->name);
        $this->assertNotNull($this->user->avatar_url);

        $rawPath = $this->user->getRawOriginal('avatar_url');
        Storage::disk('public')->assertExists($rawPath);
    }

    public function test_profile_photo_upload_rejects_invalid_file_and_oversize(): void
    {
        Storage::fake('public');

        // Not an image
        $textDoc = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson(route('profile.update'), [
                'name' => 'Budi Validasi',
                'avatar' => $textDoc,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);

        // Exceeds 2MB (2049KB)
        $bigImage = UploadedFile::fake()->create('big.jpg', 2049, 'image/jpeg');

        $responseOversize = $this->actingAs($this->user)
            ->postJson(route('profile.update'), [
                'name' => 'Budi Validasi 2',
                'avatar' => $bigImage,
            ]);

        $responseOversize->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_user_can_remove_profile_photo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('initial.jpg', 200, 200);
        $path = $file->store('avatars', 'public');

        $this->user->update([
            'avatar_url' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->user)
            ->postJson(route('profile.update'), [
                'name' => 'Budi Santoso',
                'remove_avatar' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $this->user->id,
                    'avatar_url' => null,
                ],
            ]);

        $this->user->refresh();
        $this->assertNull($this->user->avatar_url);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->postJson(route('profile.update'), [
            'name' => 'Anonim',
        ]);

        $response->assertStatus(401);
    }
}
