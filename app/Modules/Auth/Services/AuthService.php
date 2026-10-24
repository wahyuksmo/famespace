<?php

namespace App\Modules\Auth\Services;

use App\Models\Family;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Family\Repositories\FamilyRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepo,
        protected FamilyRepository $familyRepo
    ) {}

    public function login(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt(['email' => $email, 'password' => $password], $remember);
    }

    public function demoLogin(string $email): ?User
    {
        $user = $this->userRepo->findByEmail($email);
        if ($user) {
            Auth::login($user);

            return $user;
        }

        return null;
    }

    public function registerFamilyAndAdmin(array $validated): User
    {
        return DB::transaction(function () use ($validated) {
            // 1. Generate Invite Code Unik (FAM-XXXX)
            do {
                $inviteCode = 'FAM-'.strtoupper(Str::random(4));
            } while (Family::where('invite_code', $inviteCode)->exists());

            // 2. Buat Master Ruang Keluarga
            $family = $this->familyRepo->create([
                'name' => $validated['family_name'],
                'invite_code' => $inviteCode,
                'monthly_budget' => str_replace(['.', ','], ['', '.'], $validated['monthly_budget'] ?? '0'),
                'currency' => $validated['currency'] ?? 'IDR',
            ]);

            // 3. Buat Paket Default (Free)
            $freePlan = Plan::firstOrCreate(['code' => 'free'], [
                'name' => 'FamKoji Free',
                'price' => 0.00,
                'duration_days' => 3650,
                'features' => ['overview', 'chores', 'finance', 'members'],
            ]);

            Subscription::create([
                'family_id' => $family->id,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addYears(10),
            ]);

            // 4. Buat Akun Admin Pertama
            $user = $this->userRepo->create([
                'family_id' => $family->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? 'Ayah',
                'points' => 50, // Bonus pendaftaran
            ]);

            Auth::login($user);

            return $user;
        });
    }

    public function joinFamily(string $familyId, array $validated): User
    {
        return DB::transaction(function () use ($familyId, $validated) {
            $user = $this->userRepo->create([
                'family_id' => $familyId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'points' => 20,
            ]);

            Auth::login($user);

            return $user;
        });
    }
}
