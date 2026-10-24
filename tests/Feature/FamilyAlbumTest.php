<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\AlbumMedia;
use App\Models\Family;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FamilyAlbumTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $family;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->family = Family::create([
            'name' => 'Keluarga Album Test',
            'invite_code' => 'ALB-'.rand(1000, 9999),
            'monthly_budget' => 5000000,
            'currency' => 'IDR',
        ]);

        $this->user = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ayah Hebat',
            'email' => 'ayah.album.'.rand(1000, 9999).'@test.com',
            'password' => bcrypt('password123'),
            'role' => 'Ayah',
        ]);
    }

    public function test_authenticated_user_can_view_album_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('albums.index'));

        $response->assertOk()
            ->assertViewIs('modules.album.index')
            ->assertSee('Album Keluarga')
            ->assertSee('FREE TIER');
    }

    public function test_user_can_create_album(): void
    {
        $response = $this->actingAs($this->user)->post(route('albums.store'), [
            'title' => 'Liburan ke Bali 2026',
            'description' => 'Momen menyenangkan bersama anak-anak di pantai.',
        ]);

        $this->assertDatabaseHas('m_albums', [
            'family_id' => $this->family->id,
            'title' => 'Liburan ke Bali 2026',
        ]);

        $album = Album::where('family_id', $this->family->id)->first();
        $response->assertRedirect(route('albums.show', $album));

        $showResponse = $this->actingAs($this->user)->get(route('albums.show', $album));
        $showResponse->assertOk()
            ->assertViewIs('modules.album.show')
            ->assertSee('Liburan ke Bali 2026');
    }

    public function test_free_tier_can_upload_up_to_10_photos_and_11th_fails(): void
    {
        $album = Album::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'title' => 'Album Kenangan',
        ]);

        // Upload 10 photos
        for ($i = 1; $i <= 10; $i++) {
            $file = UploadedFile::fake()->image("photo_{$i}.jpg", 600, 600);
            $response = $this->actingAs($this->user)
                ->post(route('albums.media.upload', $album), [
                    'media_file' => $file,
                    'caption' => "Foto ke {$i}",
                ]);

            $response->assertSessionHas('success');
        }

        $this->assertEquals(10, $this->family->fresh()->photoCount());

        // 11th photo must fail for Free tier
        $eleventh = UploadedFile::fake()->image('photo_11.jpg', 600, 600);
        $response = $this->actingAs($this->user)
            ->post(route('albums.media.upload', $album), [
                'media_file' => $eleventh,
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(10, $this->family->fresh()->photoCount());
    }

    public function test_free_tier_can_upload_1_video_and_second_video_fails(): void
    {
        $album = Album::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'title' => 'Album Video',
        ]);

        // First video succeeds
        $video1 = UploadedFile::fake()->create('video_1.mp4', 2048, 'video/mp4');
        $response = $this->actingAs($this->user)
            ->post(route('albums.media.upload', $album), [
                'media_file' => $video1,
                'caption' => 'Video pertama',
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(1, $this->family->fresh()->videoCount());

        // Second video fails
        $video2 = UploadedFile::fake()->create('video_2.mp4', 2048, 'video/mp4');
        $response = $this->actingAs($this->user)
            ->post(route('albums.media.upload', $album), [
                'media_file' => $video2,
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, $this->family->fresh()->videoCount());
    }

    public function test_pro_tier_has_unlimited_photos_and_videos(): void
    {
        // Upgrade family to PRO
        $proPlan = Plan::firstOrCreate(
            ['code' => 'pro_monthly'],
            [
                'name' => 'Famespace Pro Bulanan',
                'price' => 20000,
                'duration_days' => 30,
                'is_active' => true,
            ]
        );

        Subscription::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->assertTrue($this->family->fresh()->isPro());

        $album = Album::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'title' => 'Album Pro',
        ]);

        // Prepopulate 10 photos
        for ($i = 1; $i <= 10; $i++) {
            AlbumMedia::create([
                'album_id' => $album->id,
                'family_id' => $this->family->id,
                'user_id' => $this->user->id,
                'media_type' => 'photo',
                'file_path' => "albums/photo_{$i}.jpg",
                'file_name' => "photo_{$i}.jpg",
                'file_size' => 1000,
            ]);
        }

        // Upload 11th photo on PRO - should succeed
        $photo11 = UploadedFile::fake()->image('photo_11.jpg', 600, 600);
        $response = $this->actingAs($this->user)
            ->post(route('albums.media.upload', $album), [
                'media_file' => $photo11,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(11, $this->family->fresh()->photoCount());

        // Prepopulate 1 video
        AlbumMedia::create([
            'album_id' => $album->id,
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'media_type' => 'video',
            'file_path' => 'albums/video_1.mp4',
            'file_name' => 'video_1.mp4',
            'file_size' => 5000,
        ]);

        // Upload 2nd video on PRO - should succeed
        $video2 = UploadedFile::fake()->create('video_2.mp4', 2048, 'video/mp4');
        $response = $this->actingAs($this->user)
            ->post(route('albums.media.upload', $album), [
                'media_file' => $video2,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(2, $this->family->fresh()->videoCount());
    }

    public function test_user_can_delete_media_and_quota_is_freed(): void
    {
        $album = Album::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'title' => 'Album Hapus Media',
        ]);

        $photo = AlbumMedia::create([
            'album_id' => $album->id,
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'media_type' => 'photo',
            'file_path' => 'albums/dummy.jpg',
            'file_name' => 'dummy.jpg',
            'file_size' => 1000,
        ]);

        $this->assertEquals(1, $this->family->fresh()->photoCount());

        $response = $this->actingAs($this->user)
            ->delete(route('albums.media.destroy', $photo));

        $response->assertSessionHas('success');
        $this->assertEquals(0, $this->family->fresh()->photoCount());
        $this->assertDatabaseMissing('t_album_media', ['id' => $photo->id]);
    }
}
