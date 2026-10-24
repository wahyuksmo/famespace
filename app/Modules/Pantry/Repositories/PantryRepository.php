<?php

namespace App\Modules\Pantry\Repositories;

use App\Models\PantryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PantryRepository
{
    public function getFamilyItems(
        string $familyId,
        ?string $filter = null,
        ?string $category = null,
        ?string $search = null,
        ?string $sort = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = PantryItem::with('creator')
            ->where('family_id', $familyId);

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('category', 'like', $term)
                    ->orWhere('notes', 'like', $term);
            });
        }

        if ($filter === 'low_stock') {
            $query->whereColumn('quantity', '<=', 'min_quantity');
        } elseif ($filter === 'expiring_soon') {
            $query->whereNotNull('expired_at')
                ->where('expired_at', '>=', now()->toDateString())
                ->where('expired_at', '<=', now()->addDays(7)->toDateString());
        } elseif ($filter === 'expired') {
            $query->whereNotNull('expired_at')
                ->where('expired_at', '<', now()->toDateString());
        }

        match ($sort) {
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'quantity_asc' => $query->orderBy('quantity', 'asc'),
            'quantity_desc' => $query->orderBy('quantity', 'desc'),
            'expired_asc' => $query->orderByRaw('CASE WHEN expired_at IS NULL THEN 1 ELSE 0 END, expired_at ASC'),
            'latest' => $query->latest('id'),
            default => $query->orderBy('category')->orderBy('name'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getDataTablesItems(
        string $familyId,
        int $start = 0,
        int $length = 10,
        ?string $search = null,
        ?string $filter = null,
        ?string $category = null,
        ?string $orderColumn = null,
        string $orderDir = 'asc'
    ): array {
        $query = PantryItem::with('creator')
            ->where('family_id', $familyId);

        $recordsTotal = (clone $query)->count();

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($filter === 'low_stock') {
            $query->whereColumn('quantity', '<=', 'min_quantity');
        } elseif ($filter === 'expiring_soon') {
            $query->whereNotNull('expired_at')
                ->where('expired_at', '>=', now()->toDateString())
                ->where('expired_at', '<=', now()->addDays(7)->toDateString());
        } elseif ($filter === 'expired') {
            $query->whereNotNull('expired_at')
                ->where('expired_at', '<', now()->toDateString());
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        $allowedColumns = ['name', 'category', 'quantity', 'min_quantity', 'expired_at'];
        if ($orderColumn && in_array($orderColumn, $allowedColumns, true)) {
            $dir = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($orderColumn, $dir);
        } else {
            $query->orderBy('category')->orderBy('name');
        }

        $data = $query->skip($start)
            ->take($length)
            ->get();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function findById(int $id, string $familyId): ?PantryItem
    {
        return PantryItem::where('id', $id)
            ->where('family_id', $familyId)
            ->first();
    }

    public function create(array $data): PantryItem
    {
        return PantryItem::create($data);
    }

    public function update(PantryItem $item, array $data): PantryItem
    {
        $item->update($data);

        return $item->fresh();
    }

    public function delete(PantryItem $item): bool
    {
        return (bool) $item->delete();
    }

    public function adjustQuantity(PantryItem $item, float $delta): PantryItem
    {
        $newQuantity = max(0, (float) $item->quantity + $delta);
        $item->update(['quantity' => $newQuantity]);

        return $item->fresh();
    }

    public function getSummaryCounts(string $familyId): array
    {
        $total = PantryItem::where('family_id', $familyId)->count();

        $lowStock = PantryItem::where('family_id', $familyId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->count();

        $expiringSoon = PantryItem::where('family_id', $familyId)
            ->whereNotNull('expired_at')
            ->where('expired_at', '>=', now()->toDateString())
            ->where('expired_at', '<=', now()->addDays(7)->toDateString())
            ->count();

        $expired = PantryItem::where('family_id', $familyId)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now()->toDateString())
            ->count();

        return [
            'total' => $total,
            'low_stock' => $lowStock,
            'expiring_soon' => $expiringSoon,
            'expired' => $expired,
        ];
    }
}
