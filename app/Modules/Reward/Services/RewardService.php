<?php

namespace App\Modules\Reward\Services;

use App\Models\Reward;
use App\Models\RewardClaim;
use App\Models\User;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Reward\Repositories\RewardRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class RewardService
{
    public function __construct(
        protected RewardRepository $rewardRepo,
        protected UserRepository $userRepo
    ) {}

    public function createReward(string $familyId, int $userId, array $data): Reward
    {
        return DB::transaction(function () use ($familyId, $userId, $data) {
            $data['family_id'] = $familyId;
            $data['created_by_user_id'] = $userId;
            $data['is_active'] = true;

            if (isset($data['stock']) && ($data['stock'] === '' || $data['stock'] === null)) {
                $data['stock'] = null;
            }

            return $this->rewardRepo->create($data);
        });
    }

    public function updateReward(int $id, string $familyId, array $data): Reward
    {
        return DB::transaction(function () use ($id, $familyId, $data) {
            $reward = $this->rewardRepo->findInFamily($id, $familyId);
            if (! $reward) {
                throw new Exception('Hadiah tidak ditemukan.');
            }

            if (isset($data['stock']) && ($data['stock'] === '' || $data['stock'] === null)) {
                $data['stock'] = null;
            }

            return $this->rewardRepo->update($reward, $data);
        });
    }

    public function deleteReward(int $id, string $familyId): void
    {
        DB::transaction(function () use ($id, $familyId) {
            $reward = $this->rewardRepo->findInFamily($id, $familyId);
            if (! $reward) {
                throw new Exception('Hadiah tidak ditemukan.');
            }

            $this->rewardRepo->delete($reward);
        });
    }

    public function claimReward(int $rewardId, User $user): RewardClaim
    {
        return DB::transaction(function () use ($rewardId, $user) {
            $reward = $this->rewardRepo->findInFamily($rewardId, $user->family_id);
            if (! $reward || ! $reward->is_active) {
                throw new Exception('Hadiah tidak tersedia.');
            }

            if (! $reward->hasStock()) {
                throw new Exception('Stok hadiah sudah habis.');
            }

            if ($user->points < $reward->points) {
                throw new Exception("Poin kamu tidak mencukupi (Butuh {$reward->points} poin, kamu punya {$user->points} poin).");
            }

            // Deduct points
            $this->userRepo->updatePoints($user, -$reward->points);

            // Deduct stock if limited
            if (! is_null($reward->stock)) {
                $reward->decrement('stock');
            }

            // Create claim record
            return $this->rewardRepo->createClaim([
                'family_id' => $user->family_id,
                'reward_id' => $reward->id,
                'user_id' => $user->id,
                'points_spent' => $reward->points,
                'status' => 'pending',
            ]);
        });
    }

    public function approveClaim(int $claimId, User $parentUser): RewardClaim
    {
        return DB::transaction(function () use ($claimId, $parentUser) {
            $claim = $this->rewardRepo->findClaimInFamily($claimId, $parentUser->family_id);
            if (! $claim) {
                throw new Exception('Data klaim tidak ditemukan.');
            }

            if ($claim->status !== 'pending') {
                throw new Exception('Klaim ini sudah diproses sebelumnya.');
            }

            return $this->rewardRepo->updateClaim($claim, [
                'status' => 'approved',
                'approved_by_user_id' => $parentUser->id,
                'approved_at' => now(),
            ]);
        });
    }

    public function rejectClaim(int $claimId, User $parentUser, ?string $notes = null): RewardClaim
    {
        return DB::transaction(function () use ($claimId, $parentUser, $notes) {
            $claim = $this->rewardRepo->findClaimInFamily($claimId, $parentUser->family_id);
            if (! $claim) {
                throw new Exception('Data klaim tidak ditemukan.');
            }

            if ($claim->status !== 'pending') {
                throw new Exception('Klaim ini sudah diproses sebelumnya.');
            }

            // Refund points
            if ($claim->user) {
                $this->userRepo->updatePoints($claim->user, $claim->points_spent);
            }

            // Restore stock if limited
            if ($claim->reward && ! is_null($claim->reward->stock)) {
                $claim->reward->increment('stock');
            }

            return $this->rewardRepo->updateClaim($claim, [
                'status' => 'rejected',
                'notes' => $notes,
                'approved_by_user_id' => $parentUser->id,
                'approved_at' => now(),
            ]);
        });
    }
}
