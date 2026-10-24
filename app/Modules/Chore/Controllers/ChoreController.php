<?php

namespace App\Modules\Chore\Controllers;

use App\Events\ChoreEvent;
use App\Http\Controllers\Controller;
use App\Modules\Chore\Repositories\ChoreRepository;
use App\Modules\Chore\Services\ChoreService;
use App\Modules\Reward\Repositories\RewardRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ChoreController extends Controller
{
    public function __construct(
        protected ChoreService $choreService,
        protected ChoreRepository $choreRepo,
        protected RewardRepository $rewardRepo
    ) {}

    public function index(Request $request)
    {
        $familyId = Auth::user()->family_id;
        $pageNumber = (int) $request->input('page', 1);
        $chores = $this->choreRepo->getPaginatedFamilyChores($familyId, 5, 'page', $pageNumber);
        $allChores = $this->choreRepo->getFamilyChores($familyId);
        $user = Auth::user();
        $family = $user->family;
        $members = $family ? $family->users()->orderBy('points', 'desc')->get() : collect();
        $rewards = $this->rewardRepo->getFamilyRewards($familyId);
        $claims = $this->rewardRepo->getFamilyClaims($familyId);
        $pendingClaimsCount = $this->rewardRepo->getPendingClaimsCount($familyId);

        return view('modules.chore.index', [
            'chores' => $chores,
            'allChores' => $allChores,
            'members' => $members,
            'user' => $user,
            'family' => $family,
            'rewards' => $rewards,
            'claims' => $claims,
            'pendingClaimsCount' => $pendingClaimsCount,
        ]);
    }

    public function paginate(Request $request, ?int $page = null)
    {
        $familyId = Auth::user()->family_id;

        if ($request->has('draw') || $request->has('load_more')) {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 5);
            $searchParam = $request->input('search');
            $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');
            $status = (string) $request->input('status', '');

            $dt = $this->choreRepo->getDataTablesChores($familyId, $start, $length, $search, $status);

            $formatted = $dt['data']->map(function ($c) {
                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'description' => $c->description,
                    'points' => (int) $c->points,
                    'status' => $c->status,
                    'due_date' => $c->due_date,
                    'formatted_due' => $c->due_date ? Carbon::parse($c->due_date)->locale('id')->isoFormat('D MMM Y') : null,
                    'assigned_to_user_id' => $c->assigned_to_user_id,
                    'assignee_name' => $c->assignee ? $c->assignee->name : 'Semua Anggota',
                    'attachment_url' => $c->attachment_path ? (str_starts_with($c->attachment_path, 'http') ? $c->attachment_path : asset('storage/'.$c->attachment_path)) : null,
                    'toggle_url' => route('chores.toggle', $c->id),
                    'update_url' => route('chores.update', $c->id),
                    'destroy_url' => route('chores.destroy', $c->id),
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
        $perPage = (int) $request->input('per_page', 5);
        $chores = $this->choreRepo->getPaginatedFamilyChores($familyId, $perPage, 'page', $pageNumber);

        if ($request->expectsJson() || $request->ajax()) {
            $res = $chores->toArray();
            $res['success'] = true;

            return response()->json($res);
        }

        return redirect()->route('chores.index', ['page' => $pageNumber]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:m_users,id',
            'points' => 'required|integer|min:1|max:500',
            'due_date' => 'nullable|date',
            'attachment' => 'nullable|image',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $this->choreService->createChore($familyId, $validated, $request->file('attachment'));
            $pendingCount = $this->choreRepo->getPendingCount($familyId);

            broadcast(new ChoreEvent(
                familyId: (string) $familyId,
                action: 'created',
                userId: (int) Auth::id(),
                userName: (string) (Auth::user()->name ?? 'Anggota'),
                message: (Auth::user()->name ?? 'Anggota').' menambahkan tugas baru: '.$validated['title'],
                data: ['pending_count' => $pendingCount]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tugas berhasil ditambahkan!',
                    'pending_count' => $pendingCount,
                ]);
            }

            return redirect()->route('chores.index')->with('success', 'Tugas baru berhasil ditambahkan!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('chores.index')->with('error', $e->getMessage());
        }
    }

    public function update(int $id, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'assigned_to_user_id' => 'nullable|exists:m_users,id',
            'points' => 'required|integer|min:1|max:500',
            'due_date' => 'nullable|date',
            'remove_attachment' => 'nullable',
            'attachment' => 'nullable|image',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $this->choreService->updateChore($id, $familyId, $validated, $request->file('attachment'));
            $pendingCount = $this->choreRepo->getPendingCount($familyId);

            broadcast(new ChoreEvent(
                familyId: (string) $familyId,
                action: 'updated',
                userId: (int) Auth::id(),
                userName: (string) (Auth::user()->name ?? 'Anggota'),
                message: (Auth::user()->name ?? 'Anggota').' memperbarui tugas: '.$validated['title'],
                data: ['id' => $id, 'pending_count' => $pendingCount]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tugas berhasil diperbarui!',
                    'pending_count' => $pendingCount,
                ]);
            }

            return redirect()->route('chores.index')->with('success', 'Tugas berhasil diperbarui!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('chores.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id, Request $request)
    {
        $familyId = Auth::user()->family_id;

        try {
            $this->choreService->deleteChore($id, $familyId);
            $pendingCount = $this->choreRepo->getPendingCount($familyId);

            broadcast(new ChoreEvent(
                familyId: (string) $familyId,
                action: 'deleted',
                userId: (int) Auth::id(),
                userName: (string) (Auth::user()->name ?? 'Anggota'),
                message: (Auth::user()->name ?? 'Anggota').' menghapus tugas.',
                data: ['id' => $id, 'pending_count' => $pendingCount]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tugas berhasil dihapus!',
                    'pending_count' => $pendingCount,
                ]);
            }

            return redirect()->route('chores.index')->with('success', 'Tugas berhasil dihapus!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('chores.index')->with('error', $e->getMessage());
        }
    }

    public function toggle(int $id, Request $request)
    {
        try {
            $result = $this->choreService->toggleChoreStatus($id);
            $familyId = Auth::user()->family_id;
            $pendingCount = $this->choreRepo->getPendingCount($familyId);

            $chore = $result['chore'];
            $assignee = $chore->assigned_to_user_id ? $chore->assignee?->fresh() : null;
            $currentUser = Auth::user()->fresh();

            broadcast(new ChoreEvent(
                familyId: (string) $familyId,
                action: 'toggled',
                userId: (int) Auth::id(),
                userName: (string) (Auth::user()->name ?? 'Anggota'),
                message: (Auth::user()->name ?? 'Anggota').' menandai tugas '.($result['new_status'] === 'completed' ? 'selesai' : 'tertunda').'.',
                data: [
                    'id' => $id,
                    'status' => $result['new_status'],
                    'pending_count' => $pendingCount,
                    'user_points' => $currentUser?->points ?? 0,
                    'assignee_id' => $chore->assigned_to_user_id,
                    'assignee_points' => $assignee?->points ?? 0,
                ]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => $result['new_status'],
                    'points_awarded' => $result['points_awarded'],
                    'pending_count' => $pendingCount,
                    'user_points' => Auth::user()->fresh()?->points ?? 0,
                ]);
            }

            return redirect()->route('chores.index')->with('success', 'Status tugas diperbarui.');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('chores.index')->with('error', $e->getMessage());
        }
    }
}
