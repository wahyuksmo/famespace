<?php

namespace App\Modules\Chore\Services;

use App\Models\Chore;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Chore\Repositories\ChoreRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChoreService
{
    public function __construct(
        protected ChoreRepository $choreRepo,
        protected UserRepository $userRepo
    ) {}

    public function createChore(string $familyId, array $data, ?UploadedFile $attachment = null): Chore
    {
        return DB::transaction(function () use ($familyId, $data, $attachment) {
            $data['family_id'] = $familyId;
            $data['status'] = 'pending';

            if ($attachment && $attachment->isValid()) {
                $data['attachment_path'] = $attachment->store('chores', 'public');
            }

            return $this->choreRepo->create($data);
        });
    }

    public function updateChore(int $id, string $familyId, array $data, ?UploadedFile $attachment = null): Chore
    {
        return DB::transaction(function () use ($id, $familyId, $data, $attachment) {
            $chore = $this->choreRepo->findInFamily($id, $familyId);
            if (! $chore) {
                throw new Exception('Tugas tidak ditemukan.');
            }

            if (! empty($data['remove_attachment']) && $chore->attachment_path) {
                Storage::disk('public')->delete($chore->attachment_path);
                $data['attachment_path'] = null;
            }
            unset($data['remove_attachment']);

            if ($attachment && $attachment->isValid()) {
                if ($chore->attachment_path) {
                    Storage::disk('public')->delete($chore->attachment_path);
                }
                $data['attachment_path'] = $attachment->store('chores', 'public');
            }

            return $this->choreRepo->update($chore, $data);
        });
    }

    public function deleteChore(int $id, string $familyId): void
    {
        DB::transaction(function () use ($id, $familyId) {
            $chore = $this->choreRepo->findInFamily($id, $familyId);
            if (! $chore) {
                throw new Exception('Tugas tidak ditemukan.');
            }

            if ($chore->attachment_path) {
                Storage::disk('public')->delete($chore->attachment_path);
            }

            // If completed, revert awarded points from user
            if ($chore->status === 'completed' && $chore->assigned_to_user_id && $chore->assignee) {
                $this->userRepo->updatePoints($chore->assignee, -$chore->points);
            }

            $this->choreRepo->delete($chore);
        });
    }

    public function toggleChoreStatus(int $choreId): array
    {
        return DB::transaction(function () use ($choreId) {
            $chore = $this->choreRepo->findById($choreId);
            if (! $chore) {
                throw new Exception('Tugas tidak ditemukan.');
            }

            $wasPending = $chore->status === 'pending';
            $newStatus = $wasPending ? 'completed' : 'pending';
            $completedAt = $wasPending ? now() : null;

            $this->choreRepo->update($chore, [
                'status' => $newStatus,
                'completed_at' => $completedAt,
            ]);

            // Award or deduct points to assigned user
            $pointsAwarded = 0;
            if ($chore->assigned_to_user_id && $chore->assignee) {
                $pointsAwarded = $wasPending ? $chore->points : -$chore->points;
                $this->userRepo->updatePoints($chore->assignee, $pointsAwarded);
            }

            return [
                'chore' => $chore,
                'new_status' => $newStatus,
                'points_awarded' => $pointsAwarded,
            ];
        });
    }
}
