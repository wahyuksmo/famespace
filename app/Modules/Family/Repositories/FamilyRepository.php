<?php

namespace App\Modules\Family\Repositories;

use App\Models\Family;

class FamilyRepository
{
    public function create(array $data): Family
    {
        return Family::create($data);
    }

    public function findById(string $id): ?Family
    {
        return Family::with(['users', 'activeSubscription.plan'])->find($id);
    }

    public function findByInviteCode(string $code): ?Family
    {
        return Family::where('invite_code', $code)->first();
    }
}
