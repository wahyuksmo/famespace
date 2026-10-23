<?php

namespace App\Modules\Auth\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function updatePoints(User $user, int $pointsDelta): User
    {
        $user->points = max(0, $user->points + $pointsDelta);
        $user->save();

        return $user;
    }
}
