<?php

namespace App\Modules\Reward\Repositories;

use App\Models\Reward;
use App\Models\RewardClaim;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RewardRepository
{
    public function getFamilyRewards(string $familyId, bool $activeOnly = true): Collection
    {
        $query = Reward::with('creator')
            ->where('family_id', $familyId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('points', 'asc')
            ->orderBy('title', 'asc')
            ->get();
    }

    public function getDataTablesRewards(string $familyId, int $start = 0, int $length = 6, ?string $search = null): array
    {
        $query = Reward::with('creator')
            ->where('family_id', $familyId)
            ->where('is_active', true);

        $recordsTotal = (clone $query)->count();

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        $data = $query->orderBy('points', 'asc')
            ->orderBy('title', 'asc')
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function getPaginatedFamilyRewards(string $familyId, int $perPage = 6, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return Reward::with('creator')
            ->where('family_id', $familyId)
            ->where('is_active', true)
            ->orderBy('points', 'asc')
            ->orderBy('title', 'asc')
            ->paginate($perPage, ['*'], $pageName, $page);
    }

    public function findInFamily(int $id, string $familyId): ?Reward
    {
        return Reward::where('id', $id)
            ->where('family_id', $familyId)
            ->first();
    }

    public function create(array $data): Reward
    {
        return Reward::create($data);
    }

    public function update(Reward $reward, array $data): Reward
    {
        $reward->update($data);

        return $reward->fresh();
    }

    public function delete(Reward $reward): void
    {
        $reward->delete();
    }

    public function createClaim(array $data): RewardClaim
    {
        return RewardClaim::create($data);
    }

    public function findClaimInFamily(int $id, string $familyId): ?RewardClaim
    {
        return RewardClaim::with(['reward', 'user', 'approver'])
            ->where('id', $id)
            ->where('family_id', $familyId)
            ->first();
    }

    public function updateClaim(RewardClaim $claim, array $data): RewardClaim
    {
        $claim->update($data);

        return $claim->fresh();
    }

    public function getFamilyClaims(string $familyId, ?int $userId = null, int $limit = 30): Collection
    {
        $query = RewardClaim::with(['reward', 'user', 'approver'])
            ->where('family_id', $familyId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getPendingClaimsCount(string $familyId): int
    {
        return RewardClaim::where('family_id', $familyId)
            ->where('status', 'pending')
            ->count();
    }
}
