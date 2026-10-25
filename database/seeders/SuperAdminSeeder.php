<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'wahyuganteng@admin.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('@Admin1234!'),
                'role' => 'super_admin',
                'family_id' => null,
                'email_verified_at' => now(),
                'can_view_all_finances' => true,
                'points' => 0,
            ]
        );
    }
}
