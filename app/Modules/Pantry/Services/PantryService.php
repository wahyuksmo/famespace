<?php

namespace App\Modules\Pantry\Services;

use App\Models\PantryItem;
use App\Models\User;
use App\Modules\Pantry\Repositories\PantryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PantryService
{
    public function __construct(
        protected PantryRepository $pantryRepository
    ) {}

    public function getItems(
        string $familyId,
        ?string $filter = null,
        ?string $category = null,
        ?string $search = null,
        ?string $sort = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        return $this->pantryRepository->getFamilyItems($familyId, $filter, $category, $search, $sort, $perPage);
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
        return $this->pantryRepository->getDataTablesItems(
            $familyId,
            $start,
            $length,
            $search,
            $filter,
            $category,
            $orderColumn,
            $orderDir
        );
    }

    public function getSummary(string $familyId): array
    {
        return $this->pantryRepository->getSummaryCounts($familyId);
    }

    public function createItem(array $data, User $user): PantryItem
    {
        $data['family_id'] = $user->family_id;
        $data['created_by'] = $user->id;

        return $this->pantryRepository->create($data);
    }

    public function updateItem(int $id, array $data, User $user): PantryItem
    {
        $item = $this->pantryRepository->findById($id, $user->family_id);

        if (! $item) {
            throw ValidationException::withMessages(['item' => 'Barang pantry tidak ditemukan.']);
        }

        return $this->pantryRepository->update($item, $data);
    }

    public function adjustStock(int $id, float $delta, User $user): PantryItem
    {
        $item = $this->pantryRepository->findById($id, $user->family_id);

        if (! $item) {
            throw ValidationException::withMessages(['item' => 'Barang pantry tidak ditemukan.']);
        }

        return $this->pantryRepository->adjustQuantity($item, $delta);
    }

    public function deleteItem(int $id, User $user): bool
    {
        $item = $this->pantryRepository->findById($id, $user->family_id);

        if (! $item) {
            throw ValidationException::withMessages(['item' => 'Barang pantry tidak ditemukan.']);
        }

        if (! $item->canDelete($user)) {
            throw ValidationException::withMessages(['permission' => 'Hanya orang tua/admin yang dapat menghapus barang inventaris.']);
        }

        return $this->pantryRepository->delete($item);
    }
}
