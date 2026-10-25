<?php

namespace App\Modules\Reward\Controllers;

use App\Events\RewardEvent;
use App\Http\Controllers\Controller;
use App\Modules\Reward\Repositories\RewardRepository;
use App\Modules\Reward\Services\RewardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RewardController extends Controller
{
    public function __construct(
        protected RewardService $rewardService,
        protected RewardRepository $rewardRepo
    ) {}

    public function paginate(Request $request, ?int $page = null): JsonResponse
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        if ($request->has('draw') || $request->has('load_more')) {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 6);
            $searchParam = $request->input('search');
            $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');

            $dt = $this->rewardRepo->getDataTablesRewards($familyId, $start, $length, $search);

            $formatted = $dt['data']->map(function ($r) use ($user) {
                return [
                    'id' => $r->id,
                    'title' => $r->title,
                    'description' => $r->description,
                    'points' => (int) $r->points,
                    'icon' => $r->icon ?? '🎁',
                    'stock' => $r->stock,
                    'has_stock' => $r->hasStock(),
                    'stock_text' => is_null($r->stock) ? 'Tak Terbatas' : ($r->stock > 0 ? "Sisa {$r->stock}" : 'Habis'),
                    'can_afford' => ($user->points ?? 0) >= $r->points,
                    'points_needed' => max(0, $r->points - ($user->points ?? 0)),
                    'update_url' => route('rewards.update', $r->id),
                    'destroy_url' => route('rewards.destroy', $r->id),
                ];
            });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $dt['recordsTotal'],
                'recordsFiltered' => $dt['recordsFiltered'],
                'data' => $formatted,
                'current_page' => (int) floor($start / max($length, 1)) + 1,
                'total' => $dt['recordsTotal'],
            ]);
        }

        $pageNumber = (int) ($page ?? $request->route('page') ?? $request->input('page', 1));
        $perPage = (int) $request->input('per_page', 6);
        $rewards = $this->rewardRepo->getPaginatedFamilyRewards($familyId, $perPage, 'page', $pageNumber);

        $res = $rewards->toArray();
        $res['success'] = true;

        return response()->json($res);
    }

    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        $rewards = $this->rewardRepo->getFamilyRewards($familyId);
        $claims = $this->rewardRepo->getFamilyClaims($familyId);
        $pendingCount = $this->rewardRepo->getPendingClaimsCount($familyId);

        return response()->json([
            'rewards' => $rewards,
            'claims' => $claims,
            'pending_count' => $pendingCount,
            'user_points' => $user->fresh()->points,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isParent()) {
            return response()->json(['message' => 'Hanya Ayah atau Ibu yang dapat membuat hadiah.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'points' => 'required|integer|min:1',
            'icon' => 'nullable|string|max:50',
            'stock' => 'nullable|integer|min:0',
        ]);

        try {
            $reward = $this->rewardService->createReward(
                $user->family_id,
                $user->id,
                $validated
            );

            broadcast(new RewardEvent(
                familyId: (string) $user->family_id,
                action: 'reward_created',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "{$user->name} menambahkan hadiah baru: {$reward->title}",
                data: ['reward_id' => $reward->id, 'title' => $reward->title]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Hadiah berhasil ditambahkan ke katalog!',
                'data' => $reward,
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isParent()) {
            return response()->json(['message' => 'Hanya Ayah atau Ibu yang dapat mengedit hadiah.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'points' => 'required|integer|min:1',
            'icon' => 'nullable|string|max:50',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $reward = $this->rewardService->updateReward(
                $id,
                $user->family_id,
                $validated
            );

            broadcast(new RewardEvent(
                familyId: (string) $user->family_id,
                action: 'reward_updated',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "{$user->name} memperbarui hadiah: {$reward->title}",
                data: ['reward_id' => $reward->id, 'title' => $reward->title]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Hadiah berhasil diperbarui!',
                'data' => $reward,
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isParent()) {
            return response()->json(['message' => 'Hanya Ayah atau Ibu yang dapat menghapus hadiah.'], 403);
        }

        try {
            $this->rewardService->deleteReward($id, $user->family_id);

            broadcast(new RewardEvent(
                familyId: (string) $user->family_id,
                action: 'reward_deleted',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "{$user->name} menghapus hadiah dari katalog.",
                data: ['reward_id' => $id]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Hadiah berhasil dihapus dari katalog.',
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function claim(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $claim = $this->rewardService->claimReward($id, $user);

            broadcast(new RewardEvent(
                familyId: (string) $user->family_id,
                action: 'reward_claimed',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "{$user->name} menukarkan poin untuk hadiah: {$claim->reward->title} 🎁",
                data: [
                    'claim_id' => $claim->id,
                    'reward_title' => $claim->reward->title,
                    'user_id' => $user->id,
                    'user_points' => $user->fresh()->points,
                ]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Selamat! Hadiah berhasil diklaim. Tunggu konfirmasi Orang Tua.',
                'data' => $claim,
                'user_points' => $user->fresh()->points,
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isParent()) {
            return response()->json(['message' => 'Hanya Ayah atau Ibu yang dapat menyetujui klaim hadiah.'], 403);
        }

        try {
            $claim = $this->rewardService->approveClaim($id, $user);

            broadcast(new RewardEvent(
                familyId: (string) $user->family_id,
                action: 'claim_approved',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "Klaim hadiah {$claim->reward->title} oleh {$claim->user->name} telah disetujui! 🎉",
                data: [
                    'claim_id' => $claim->id,
                    'reward_title' => $claim->reward->title,
                    'target_user_id' => $claim->user_id,
                ]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Klaim hadiah berhasil disetujui & diserahkan! 🎉',
                'data' => $claim,
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isParent()) {
            return response()->json(['message' => 'Hanya Ayah atau Ibu yang dapat menolak klaim hadiah.'], 403);
        }

        $notes = $request->input('notes');

        try {
            $claim = $this->rewardService->rejectClaim($id, $user, $notes);

            broadcast(new RewardEvent(
                familyId: (string) $user->family_id,
                action: 'claim_rejected',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: "Klaim hadiah {$claim->reward->title} oleh {$claim->user->name} ditolak. Poin dikembalikan.",
                data: [
                    'claim_id' => $claim->id,
                    'reward_title' => $claim->reward->title,
                    'target_user_id' => $claim->user_id,
                ]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Klaim hadiah ditolak. Poin dan stok telah dikembalikan.',
                'data' => $claim,
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
