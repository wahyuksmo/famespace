<?php

namespace App\Modules\Timeline\Repositories;

use App\Models\Category;
use App\Models\TimelineMoment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TimelineRepository
{
    public function getFamilyMoments(string $familyId): Collection
    {
        return TimelineMoment::with(['user', 'category'])
            ->where('family_id', $familyId)
            ->orderBy('event_date', 'desc')
            ->get();
    }

    public function getMomentsWithCoordinates(string $familyId, ?string $search = null): Collection
    {
        $query = TimelineMoment::with(['user', 'category'])
            ->where('family_id', $familyId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('story', 'like', "%{$term}%")
                    ->orWhere('location_name', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('category', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%");
                    });
            });
        }

        return $query->orderBy('event_date', 'desc')->get();
    }

    public function getPaginatedFamilyMoments(string $familyId, int $perPage = 4, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return TimelineMoment::with(['user', 'category'])
            ->where('family_id', $familyId)
            ->orderBy('event_date', 'desc')
            ->paginate($perPage, ['*'], $pageName, $page);
    }

    public function getDataTablesMoments(string $familyId, int $start = 0, int $length = 4, ?string $search = null): array
    {
        $query = TimelineMoment::with(['user', 'category'])
            ->where('family_id', $familyId);

        $recordsTotal = (clone $query)->count();

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('story', 'like', "%{$term}%")
                    ->orWhere('location_name', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('category', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();

        $data = $query->orderBy('event_date', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function findInFamily(int $id, string $familyId): ?TimelineMoment
    {
        return TimelineMoment::with(['user', 'category'])
            ->where('id', $id)
            ->where('family_id', $familyId)
            ->first();
    }

    public function create(array $data): TimelineMoment
    {
        return TimelineMoment::create($data);
    }

    public function update(TimelineMoment $moment, array $data): TimelineMoment
    {
        $moment->update($data);

        return $moment;
    }

    public function delete(TimelineMoment $moment): bool
    {
        return (bool) $moment->delete();
    }

    public function getCategories(string $familyId): Collection
    {
        return Category::where(function ($q) use ($familyId) {
            $q->where('family_id', $familyId)->orWhereNull('family_id');
        })->where('type', 'timeline')->orderBy('name', 'asc')->get();
    }

    public function findCategoryInFamily(int $id, string $familyId): ?Category
    {
        return Category::where('id', $id)
            ->where('type', 'timeline')
            ->where(function ($q) use ($familyId) {
                $q->where('family_id', $familyId)->orWhereNull('family_id');
            })
            ->first();
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }
}
