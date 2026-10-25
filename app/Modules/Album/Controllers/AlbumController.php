<?php

namespace App\Modules\Album\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\AlbumMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AlbumController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $family = $user->family;

        $albums = Album::where('family_id', $family->id)
            ->withCount(['photos', 'videos'])
            ->with(['user', 'photos' => function ($q) {
                $q->latest()->limit(4);
            }])
            ->latest()
            ->paginate(12);

        $quota = $family->albumQuotaInfo();

        return view('modules.album.index', [
            'family' => $family,
            'user' => $user,
            'albums' => $albums,
            'quota' => $quota,
        ]);
    }

    public function show(Album $album, Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $family = $user->family;

        if ($album->family_id !== $family->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $typeFilter = $request->query('type', 'all');

        $mediaQuery = $album->media()->with('user')->latest();
        if (in_array($typeFilter, ['photo', 'video'])) {
            $mediaQuery->where('media_type', $typeFilter);
        }

        $media = $mediaQuery->paginate(24);
        $quota = $family->albumQuotaInfo();

        $mediaItems = $media->getCollection()->map(fn ($m) => [
            'id' => $m->id,
            'is_photo' => $m->isPhoto(),
            'url' => $m->file_url,
            'caption' => $m->caption,
            'file_name' => $m->file_name,
            'uploader' => $m->user?->name ?? 'Anggota',
            'date' => $m->created_at->translatedFormat('d M Y, H:i'),
            'size' => $m->formatted_size,
            'is_banner' => $family->banner_media_id === $m->id,
        ])->values();

        return view('modules.album.show', [
            'family' => $family,
            'user' => $user,
            'album' => $album,
            'media' => $media,
            'mediaItems' => $mediaItems,
            'quota' => $quota,
            'typeFilter' => $typeFilter,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $family = $user->family;

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('albums/covers', 'public');
        }

        $album = Album::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'cover_image_path' => $coverPath,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Album berhasil dibuat!',
                'album' => $album,
            ]);
        }

        return redirect()->route('albums.show', $album)->with('success', 'Album keluarga "'.$album->title.'" berhasil dibuat!');
    }

    public function update(Request $request, Album $album): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($album->family_id !== $user->family_id) {
            abort(403);
        }

        if (! $user->isParent() && $album->user_id !== $user->id) {
            abort(403, 'Hanya pembuat album atau orang tua yang dapat mengubah album.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($album->cover_image_path && Storage::disk('public')->exists($album->cover_image_path)) {
                Storage::disk('public')->delete($album->cover_image_path);
            }
            $album->cover_image_path = $request->file('cover_image')->store('albums/covers', 'public');
        }

        $album->title = $validated['title'];
        $album->description = $validated['description'] ?? null;
        $album->save();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Album berhasil diperbarui!',
                'album' => $album,
            ]);
        }

        return redirect()->back()->with('success', 'Album berhasil diperbarui!');
    }

    public function destroy(Album $album): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($album->family_id !== $user->family_id) {
            abort(403);
        }

        if (! $user->isParent() && $album->user_id !== $user->id) {
            abort(403, 'Hanya pembuat album atau orang tua yang dapat menghapus album.');
        }

        // Delete cover image
        if ($album->cover_image_path && Storage::disk('public')->exists($album->cover_image_path)) {
            Storage::disk('public')->delete($album->cover_image_path);
        }

        // Delete media files
        foreach ($album->media as $mediaItem) {
            if (Storage::disk('public')->exists($mediaItem->file_path)) {
                Storage::disk('public')->delete($mediaItem->file_path);
            }
        }

        $albumTitle = $album->title;
        $album->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Album "'.$albumTitle.'" berhasil dihapus.',
            ]);
        }

        return redirect()->route('albums.index')->with('success', 'Album "'.$albumTitle.'" berhasil dihapus.');
    }

    public function uploadMedia(Request $request, Album $album): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $family = $user->family;

        if ($album->family_id !== $family->id) {
            abort(403);
        }

        $request->validate([
            'media_file' => 'required|file',
            'caption' => 'nullable|string|max:500',
        ]);

        $file = $request->file('media_file');
        $mime = $file->getMimeType();
        $isPhoto = str_starts_with($mime, 'image/');
        $isVideo = str_starts_with($mime, 'video/');

        if (! $isPhoto && ! $isVideo) {
            return $this->errorResponse($request, 'Format berkas tidak didukung. Harap unggah foto (JPG/PNG/WEBP) atau video (MP4/MOV/WEBM).');
        }

        if ($isPhoto) {
            if (! $family->canUploadPhoto()) {
                return $this->errorResponse(
                    $request,
                    'Batas Free tier tercapai: Maksimal 10 foto untuk keluarga Free. Upgrade ke Pro untuk upload tanpa batas!',
                    'QUOTA_EXCEEDED'
                );
            }
        } else {
            if (! $family->canUploadVideo()) {
                return $this->errorResponse(
                    $request,
                    'Batas Free tier tercapai: Maksimal 1 video untuk keluarga Free. Upgrade ke Pro untuk upload tanpa batas!',
                    'QUOTA_EXCEEDED'
                );
            }
        }

        $path = $file->store("albums/{$family->id}/{$album->id}", 'public');

        $media = AlbumMedia::create([
            'album_id' => $album->id,
            'family_id' => $family->id,
            'user_id' => $user->id,
            'media_type' => $isPhoto ? 'photo' : 'video',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'caption' => $request->input('caption'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => ($isPhoto ? 'Foto' : 'Video').' berhasil diunggah!',
                'media' => $media,
                'quota' => $family->fresh()->albumQuotaInfo(),
            ]);
        }

        return redirect()->back()->with('success', ($isPhoto ? 'Foto' : 'Video').' berhasil diunggah ke album!');
    }

    public function destroyMedia(AlbumMedia $media): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($media->family_id !== $user->family_id) {
            abort(403);
        }

        if (! $user->isParent() && $media->user_id !== $user->id && $media->album->user_id !== $user->id) {
            abort(403, 'Hanya pengunggah, pemilik album, atau orang tua yang dapat menghapus media ini.');
        }

        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $type = $media->isPhoto() ? 'Foto' : 'Video';
        $media->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "{$type} berhasil dihapus.",
                'quota' => $user->family->fresh()->albumQuotaInfo(),
            ]);
        }

        return redirect()->back()->with('success', "{$type} berhasil dihapus dari album.");
    }

    public function setBanner(Request $request, AlbumMedia $media): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $family = $user->family;

        if ($media->family_id !== $family->id) {
            abort(403);
        }

        if (! $media->isPhoto()) {
            return $this->errorResponse($request, 'Hanya foto yang dapat dijadikan banner beranda.');
        }

        $updateData = ['banner_media_id' => $media->id];
        if ($request->filled('position')) {
            $updateData['banner_position'] = $request->string('position');
        }
        if ($request->filled('fit')) {
            $updateData['banner_fit'] = $request->string('fit');
        }

        $family->update($updateData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Foto berhasil dijadikan banner beranda!',
                'banner_url' => $media->file_url,
                'banner_media_id' => $media->id,
                'banner_position' => $family->banner_position,
                'banner_fit' => $family->banner_fit,
            ]);
        }

        return redirect()->back()->with('success', 'Foto berhasil dijadikan banner beranda!');
    }

    public function updateBannerPosition(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $family = $user->family;

        $validated = $request->validate([
            'position' => ['nullable', 'string', 'max:50'],
            'fit' => ['nullable', 'string', 'in:cover,contain'],
        ]);

        $family->update([
            'banner_position' => $validated['position'] ?? $family->banner_position ?? '50% 50%',
            'banner_fit' => $validated['fit'] ?? $family->banner_fit ?? 'cover',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Posisi banner berhasil disimpan.',
                'banner_position' => $family->banner_position,
                'banner_fit' => $family->banner_fit,
            ]);
        }

        return redirect()->back()->with('success', 'Posisi banner berhasil diperbarui.');
    }

    public function removeBanner(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $family = $user->family;

        $family->update([
            'banner_media_id' => null,
            'banner_position' => '50% 50%',
            'banner_fit' => 'cover',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Banner beranda berhasil direset.',
            ]);
        }

        return redirect()->back()->with('success', 'Banner beranda berhasil direset.');
    }

    protected function errorResponse(Request $request, string $message, string $code = 'VALIDATION_ERROR'): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'code' => $code,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->withInput()->with('error', $message)->with('quota_error', $code === 'QUOTA_EXCEEDED');
    }
}
