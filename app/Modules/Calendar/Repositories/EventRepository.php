<?php

namespace App\Modules\Calendar\Repositories;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventRepository
{
    public function getFamilyEvents(string $familyId): Collection
    {
        return Event::with('creator')
            ->where('family_id', $familyId)
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function getPaginatedFamilyEvents(string $familyId, int $perPage = 5, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return Event::with('creator')
            ->where('family_id', $familyId)
            ->orderBy('start_time', 'asc')
            ->paginate($perPage, ['*'], $pageName, $page);
    }

    public function getDataTablesEvents(string $familyId, int $start = 0, int $length = 5, ?string $search = null): array
    {
        $query = Event::with('creator')
            ->where('family_id', $familyId);

        $recordsTotal = (clone $query)->count();

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        $data = $query->orderBy('start_time', 'asc')
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function findInFamily(int $id, string $familyId): ?Event
    {
        return Event::with('creator')
            ->where('id', $id)
            ->where('family_id', $familyId)
            ->first();
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event;
    }

    public function delete(Event $event): bool
    {
        return (bool) $event->delete();
    }
}
