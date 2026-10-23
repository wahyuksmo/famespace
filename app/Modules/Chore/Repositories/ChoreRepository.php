<?php

namespace App\Modules\Chore\Repositories;

use App\Models\Chore;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChoreRepository
{
    public function getFamilyChores(string $familyId): Collection
    {
        return Chore::with('assignee')
            ->where('family_id', $familyId)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getPaginatedFamilyChores(string $familyId, int $perPage = 5, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return Chore::with('assignee')
            ->where('family_id', $familyId)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->paginate($perPage, ['*'], $pageName, $page);
    }

    public function getDataTablesChores(string $familyId, int $start = 0, int $length = 10, ?string $search = null, ?string $status = null): array
    {
        $query = Chore::with('assignee')
            ->where('family_id', $familyId);

        if (! empty($status) && in_array($status, ['pending', 'completed'])) {
            $query->where('status', $status);
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('assignee', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();

        $data = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function create(array $data): Chore
    {
        return Chore::create($data);
    }

    public function findById(int $id): ?Chore
    {
        return Chore::with('assignee')->find($id);
    }

    public function findInFamily(int $id, string $familyId): ?Chore
    {
        return Chore::with('assignee')
            ->where('id', $id)
            ->where('family_id', $familyId)
            ->first();
    }

    public function update(Chore $chore, array $data): Chore
    {
        $chore->update($data);

        return $chore;
    }

    public function getPendingCount(string $familyId): int
    {
        return Chore::where('family_id', $familyId)
            ->where('status', 'pending')
            ->count();
    }

    public function delete(Chore $chore): bool
    {
        return (bool) $chore->delete();
    }
}
