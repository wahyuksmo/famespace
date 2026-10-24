<?php

namespace App\Modules\Timeline\Services;

use App\Models\Category;
use App\Models\TimelineMoment;
use App\Modules\Timeline\Repositories\TimelineRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TimelineService
{
    public function __construct(
        protected TimelineRepository $timelineRepo
    ) {}

    public function resolveCategoryId(string $familyId, mixed $categoryInput): ?int
    {
        if (blank($categoryInput)) {
            return null;
        }

        if (is_numeric($categoryInput)) {
            $cat = Category::where('id', (int) $categoryInput)
                ->where(function ($q) use ($familyId) {
                    $q->where('family_id', $familyId)->orWhereNull('family_id');
                })
                ->first();

            return $cat?->id;
        }

        $trimmed = trim((string) $categoryInput);
        if ($trimmed === '') {
            return null;
        }

        $cat = Category::firstOrCreate([
            'family_id' => $familyId,
            'name' => $trimmed,
            'type' => 'timeline',
        ], [
            'icon' => '📸',
            'color' => '#2F5233',
        ]);

        return $cat->id;
    }

    public function createMoment(string $familyId, int $userId, array $data, ?UploadedFile $image = null): TimelineMoment
    {
        return DB::transaction(function () use ($familyId, $userId, $data, $image) {
            $imagePath = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80';
            if ($image && $image->isValid()) {
                $imagePath = $image->store('moments', 'public');
            }

            $catInput = $data['category_id'] ?? ($data['category'] ?? null);
            $categoryId = $this->resolveCategoryId($familyId, $catInput);

            return $this->timelineRepo->create([
                'family_id' => $familyId,
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $data['title'],
                'story' => $data['story'] ?? null,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
                'longitude' => isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null,
                'image_path' => $imagePath,
                'event_date' => $data['event_date'] ?? now()->toDateString(),
            ]);
        });
    }

    public function updateMoment(int $id, string $familyId, array $data, ?UploadedFile $image = null): TimelineMoment
    {
        return DB::transaction(function () use ($id, $familyId, $data, $image) {
            $moment = $this->timelineRepo->findInFamily($id, $familyId);
            if (! $moment) {
                throw new Exception('Momen linimasa tidak ditemukan.');
            }

            $updateData = [
                'title' => $data['title'] ?? $moment->title,
                'story' => $data['story'] ?? $moment->story,
                'location_name' => array_key_exists('location_name', $data) ? $data['location_name'] : $moment->location_name,
                'latitude' => array_key_exists('latitude', $data) ? ($data['latitude'] !== null && $data['latitude'] !== '' ? (float) $data['latitude'] : null) : $moment->latitude,
                'longitude' => array_key_exists('longitude', $data) ? ($data['longitude'] !== null && $data['longitude'] !== '' ? (float) $data['longitude'] : null) : $moment->longitude,
                'event_date' => $data['event_date'] ?? $moment->event_date,
            ];

            if (array_key_exists('category_id', $data) || array_key_exists('category', $data)) {
                $catInput = $data['category_id'] ?? ($data['category'] ?? null);
                $updateData['category_id'] = $this->resolveCategoryId($familyId, $catInput);
            }

            if ($image && $image->isValid()) {
                if ($moment->image_path && ! str_starts_with($moment->image_path, 'http')) {
                    Storage::disk('public')->delete($moment->image_path);
                }
                $updateData['image_path'] = $image->store('moments', 'public');
            }

            return $this->timelineRepo->update($moment, $updateData);
        });
    }

    public function deleteMoment(int $id, string $familyId): void
    {
        DB::transaction(function () use ($id, $familyId) {
            $moment = $this->timelineRepo->findInFamily($id, $familyId);
            if (! $moment) {
                throw new Exception('Momen linimasa tidak ditemukan.');
            }

            if ($moment->image_path && ! str_starts_with($moment->image_path, 'http')) {
                Storage::disk('public')->delete($moment->image_path);
            }

            $this->timelineRepo->delete($moment);
        });
    }

    public function saveCategory(string $familyId, array $data, ?int $id = null): Category
    {
        $name = trim($data['name']);

        if ($id) {
            $cat = $this->timelineRepo->findCategoryInFamily($id, $familyId);
            if (! $cat) {
                throw new Exception('Kategori tidak ditemukan.');
            }
            if (is_null($cat->family_id)) {
                throw new Exception('Kategori bawaan sistem tidak dapat diubah.');
            }

            return $this->timelineRepo->updateCategory($cat, [
                'name' => $name,
                'icon' => $data['icon'] ?? $cat->icon,
                'color' => $data['color'] ?? $cat->color,
            ]);
        }

        return Category::firstOrCreate([
            'family_id' => $familyId,
            'name' => $name,
            'type' => 'timeline',
        ], [
            'icon' => $data['icon'] ?? '📸',
            'color' => $data['color'] ?? '#2F5233',
        ]);
    }

    public function deleteCategory(int $id, string $familyId): void
    {
        $cat = $this->timelineRepo->findCategoryInFamily($id, $familyId);
        if (! $cat) {
            throw new Exception('Kategori tidak ditemukan.');
        }

        if (is_null($cat->family_id)) {
            throw new Exception('Kategori bawaan sistem tidak dapat dihapus.');
        }

        $this->timelineRepo->deleteCategory($cat);
    }
}
