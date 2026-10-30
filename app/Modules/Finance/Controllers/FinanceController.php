<?php

namespace App\Modules\Finance\Controllers;

use App\Events\FinanceEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Finance\Repositories\FinanceRepository;
use App\Modules\Finance\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class FinanceController extends Controller
{
    public function __construct(
        protected FinanceService $financeService,
        protected FinanceRepository $financeRepo
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;
        $pageNumber = (int) $request->input('page', 1);
        $period = $request->input('period');
        $cutOffDay = (int) ($user->cut_off_day ?? 1);
        $filterUserId = $user->canViewAllFinances() ? null : $user->id;
        $overview = $this->financeService->calculateOverview($familyId, 5, 'page', $pageNumber, $period, $filterUserId, null, $cutOffDay);
        $categories = $this->financeRepo->getCategories($familyId);
        $report = $this->financeService->getFinancialReport($familyId, 'monthly', $overview['selected_period'] ?? now()->format('Y-m'), null, null, null, $cutOffDay);
        $members = $user->canViewAllFinances()
            ? ($user->family ? $user->family->users()->orderBy('points', 'desc')->get() : collect([$user]))
            : collect([$user]);

        return view('modules.finance.index', [
            'finance' => $overview,
            'categories' => $categories,
            'report' => $report,
            'members' => $members,
            'categoryBudgets' => $user->canViewAllFinances() ? ($overview['category_budgets'] ?? collect()) : collect(),
            'goals' => $user->canViewAllFinances() ? ($overview['goals'] ?? collect()) : collect(),
            'selectedPeriod' => $overview['selected_period'] ?? now()->format('Y-m'),
            'selectedPeriodLabel' => $overview['selected_period_label'] ?? now()->locale('id')->isoFormat('MMMM Y'),
            'availablePeriods' => $overview['available_periods'] ?? [],
            'canViewAllFinances' => $user->canViewAllFinances(),
            'cutOffDay' => $cutOffDay,
        ]);
    }

    public function paginate(Request $request, ?int $page = null)
    {
        $user = Auth::user();
        $familyId = $user->family_id;
        $filterUserId = $user->canViewAllFinances() ? null : $user->id;
        $cutOffDay = (int) ($user->cut_off_day ?? 1);

        if ($request->has('draw') || $request->has('load_more')) {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 5);
            $searchParam = $request->input('search');
            $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');
            $type = (string) $request->input('type', '');

            if (! $user->canViewAllFinances()) {
                $type = 'expense';
            }

            $dt = $this->financeRepo->getDataTablesTransactions($familyId, $start, $length, $search, $type, $filterUserId);

            $formatted = $dt['data']->map(function ($trx) {
                return [
                    'id' => $trx->id,
                    'transaction_date' => $trx->transaction_date,
                    'formatted_date' => Carbon::parse($trx->transaction_date)->locale('id')->isoFormat('D MMM Y'),
                    'type' => $trx->type,
                    'description' => $trx->description,
                    'amount' => (int) $trx->amount,
                    'formatted_amount' => ($trx->type === 'income' ? '+ ' : '- ').'Rp '.number_format($trx->amount, 0, ',', '.'),
                    'category' => $trx->category ? $trx->category->name : ($trx->type === 'income' ? 'Pemasukan' : ($trx->type === 'saving' ? 'Tabungan' : 'Pengeluaran')),
                    'category_icon' => $trx->category ? $trx->category->icon : ($trx->type === 'income' ? '💰' : ($trx->type === 'saving' ? '🐷' : '📦')),
                    'category_id' => $trx->category_id,
                    'user' => $trx->user ? $trx->user->name : 'Keluarga',
                    'user_id' => $trx->user_id,
                    'receipt_url' => $trx->receipt_path ? (str_starts_with($trx->receipt_path, 'http') ? $trx->receipt_path : asset('storage/'.$trx->receipt_path)) : null,
                    'update_url' => route('finances.update', $trx->id),
                    'destroy_url' => route('finances.destroy', $trx->id),
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
        $overview = $this->financeService->calculateOverview($familyId, $perPage, 'page', $pageNumber, null, $filterUserId, null, $cutOffDay);

        if ($request->expectsJson() || $request->ajax()) {
            $res = $overview['transactions']->toArray();
            $res['success'] = true;
            $res['overview'] = $overview;
            $res['transactions'] = $overview['transactions'];

            return response()->json($res);
        }

        return redirect()->route('finances.index', ['page' => $pageNumber]);
    }

    public function paginateBudgets(Request $request)
    {
        $user = Auth::user();
        if (! $user->canViewAllFinances()) {
            return response()->json(['error' => 'Akses terbatas.'], 403);
        }

        $familyId = $user->family_id;
        $cutOffDay = (int) ($user->cut_off_day ?? 1);

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 5);
        $searchParam = $request->input('search');
        $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');
        $period = (string) $request->input('period', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = now()->format('Y-m');
        }

        $status = (string) $request->input('status', '');

        $dt = $this->financeRepo->getDataTablesBudgets($familyId, $period, $start, $length, $search, $status, $cutOffDay);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $dt['recordsTotal'],
            'recordsFiltered' => $dt['recordsFiltered'],
            'data' => $dt['data'],
            'period' => $period,
        ]);
    }

    public function paginateCategories(Request $request)
    {
        $user = Auth::user();
        if (! $user->canViewAllFinances()) {
            return response()->json(['error' => 'Akses terbatas.'], 403);
        }

        $familyId = $user->family_id;

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 5);
        $searchParam = $request->input('search');
        $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) ($searchParam ?? '');
        $type = (string) $request->input('type', '');

        $dt = $this->financeRepo->getDataTablesCategories($familyId, $start, $length, $search, $type);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $dt['recordsTotal'],
            'recordsFiltered' => $dt['recordsFiltered'],
            'data' => $dt['data'],
        ]);
    }

    public function summary(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;
        $period = $request->input('period');
        $cutOffDay = (int) ($user->cut_off_day ?? 1);
        $filterUserId = $user->canViewAllFinances() ? null : $user->id;
        $overview = $this->financeService->calculateOverview($familyId, 5, 'page', 1, $period, $filterUserId, null, $cutOffDay);
        $categories = $this->financeRepo->getCategories($familyId);

        return response()->json([
            'success' => true,
            'balance' => $overview['balance'],
            'formatted_balance' => 'Rp '.number_format($overview['balance'], 0, ',', '.'),
            'income' => $overview['income'],
            'formatted_income' => 'Rp '.number_format($overview['income'], 0, ',', '.'),
            'expense' => $overview['expense'],
            'formatted_expense' => 'Rp '.number_format($overview['expense'], 0, ',', '.'),
            'saving' => $overview['saving'] ?? 0,
            'formatted_saving' => 'Rp '.number_format($overview['saving'] ?? 0, 0, ',', '.'),
            'goals' => $overview['goals'] ?? [],
            'category_budgets' => $overview['category_budgets'] ?? [],
            'selected_period' => $overview['selected_period'],
            'selected_period_label' => $overview['selected_period_label'],
            'cut_off_day' => $cutOffDay,
            'categories' => $categories->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'type' => $cat->type,
                    'icon' => $cat->icon ?: '🏷️',
                    'color' => $cat->color ?: '#2F5233',
                    'monthly_budget' => (float) ($cat->monthly_budget ?? 0),
                ];
            }),
        ]);
    }

    public function reportData(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;
        $cutOffDay = (int) ($user->cut_off_day ?? 1);

        $mode = (string) $request->input('mode', 'monthly');
        $period = $request->input('period');
        $year = $request->input('year') ? (int) $request->input('year') : null;
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');

        $report = $this->financeService->getFinancialReport($familyId, $mode, $period, $year, $startMonth, $endMonth, $cutOffDay);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    public function updateCutOff(Request $request)
    {
        $validated = $request->validate([
            'cut_off_day' => 'required|integer|min:1|max:31',
        ], [
            'cut_off_day.required' => 'Tanggal cut off wajib ditentukan.',
            'cut_off_day.min' => 'Tanggal cut off minimal 1.',
            'cut_off_day.max' => 'Tanggal cut off maksimal 31.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->cut_off_day = (int) $validated['cut_off_day'];
        $user->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tanggal cut off berhasil diubah ke tanggal '.$user->cut_off_day.'!',
                'cut_off_day' => $user->cut_off_day,
            ]);
        }

        return redirect()->route('finances.index')->with('success', 'Tanggal cut off berhasil diubah!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense,saving',
            'amount' => 'required',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable',
            'user_id' => 'nullable|exists:m_users,id',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|image',
        ]);

        $user = Auth::user();
        $familyId = $user->family_id;
        if (! $user->canViewAllFinances()) {
            $validated['type'] = 'expense';
            unset($validated['user_id']);
            $userId = $user->id;
        } else {
            $userId = ! empty($validated['user_id']) ? (int) $validated['user_id'] : $user->id;
        }

        try {
            $this->financeService->recordTransaction($familyId, (int) $userId, $validated, $request->file('receipt'));

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'transaction_created',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: $user->name.' mencatat transaksi kas baru: '.$validated['description']
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Transaksi kas berhasil dicatat!']);
            }

            return redirect()->route('finances.index')->with('success', 'Transaksi kas berhasil dicatat!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function update(int $id, Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense,saving',
            'amount' => 'required',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable',
            'user_id' => 'nullable|exists:m_users,id',
            'remove_receipt' => 'nullable',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|image',
        ]);

        $user = Auth::user();
        $familyId = $user->family_id;
        $authUserId = $user->canViewAllFinances() ? null : $user->id;

        try {
            if (! $user->canViewAllFinances()) {
                unset($validated['user_id']);
                $validated['type'] = 'expense';
            }
            $this->financeService->updateTransaction($id, $familyId, $validated, $request->file('receipt'), $authUserId);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'transaction_updated',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: $user->name.' memperbarui transaksi kas: '.$validated['description']
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Transaksi kas berhasil diperbarui!']);
            }

            return redirect()->route('finances.index')->with('success', 'Transaksi kas berhasil diperbarui!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id, Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;
        $authUserId = $user->canViewAllFinances() ? null : $user->id;

        try {
            $this->financeService->deleteTransaction($id, $familyId, $authUserId);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'transaction_deleted',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: $user->name.' menghapus transaksi kas.'
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Transaksi kas berhasil dihapus!']);
            }

            return redirect()->route('finances.index')->with('success', 'Transaksi kas berhasil dihapus!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function storeCategory(Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'monthly_budget' => 'nullable',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $cat = $this->financeService->saveCategory($familyId, $validated);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'category_created',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' menambahkan kategori anggaran baru.'
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan!', 'category' => $cat]);
            }

            return redirect()->route('finances.index')->with('success', 'Kategori berhasil ditambahkan!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function updateCategory(int $id, Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'monthly_budget' => 'nullable',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $cat = $this->financeService->saveCategory($familyId, $validated, $id);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'category_updated',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' memperbarui kategori anggaran.'
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui!', 'category' => $cat]);
            }

            return redirect()->route('finances.index')->with('success', 'Kategori berhasil diperbarui!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function destroyCategory(int $id, Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $familyId = Auth::user()->family_id;

        try {
            $this->financeService->deleteCategory($id, $familyId);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'category_deleted',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' menghapus kategori anggaran.'
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus! Transaksi dan anggaran dialihkan ke kategori Lain-lain.']);
            }

            return redirect()->route('finances.index')->with('success', 'Kategori berhasil dihapus! Transaksi dan anggaran dialihkan ke kategori Lain-lain.');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function storeGoal(Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'target_amount' => 'required',
            'current_amount' => 'nullable',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'target_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $goal = $this->financeService->saveGoal($familyId, $validated);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'goal_created',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' membuat target tabungan baru: '.$validated['title'],
                data: ['goal' => $goal]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Target tabungan berhasil dibuat!', 'goal' => $goal]);
            }

            return redirect()->route('finances.index')->with('success', 'Target tabungan berhasil dibuat!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function updateGoal(int $id, Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'target_amount' => 'required',
            'current_amount' => 'nullable',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'target_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,completed,paused',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $goal = $this->financeService->saveGoal($familyId, $validated, $id);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'goal_updated',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' memperbarui target tabungan: '.$validated['title'],
                data: ['goal' => $goal]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Target tabungan berhasil diperbarui!', 'goal' => $goal]);
            }

            return redirect()->route('finances.index')->with('success', 'Target tabungan berhasil diperbarui!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function destroyGoal(int $id, Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $familyId = Auth::user()->family_id;

        try {
            $this->financeService->deleteGoal($id, $familyId, (int) Auth::id());

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'goal_deleted',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' menghapus target tabungan.'
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Target tabungan berhasil dihapus dan saldo tabungan dikembalikan ke kas keluarga!']);
            }

            return redirect()->route('finances.index')->with('success', 'Target tabungan berhasil dihapus dan saldo tabungan dikembalikan ke kas keluarga!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function depositGoal(int $id, Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $validated = $request->validate([
            'amount' => 'required',
            'note' => 'nullable|string|max:255',
        ]);

        $familyId = Auth::user()->family_id;
        $userId = Auth::id();
        $amount = (float) str_replace(['.', ','], ['', '.'], (string) $validated['amount']);

        try {
            $goal = $this->financeService->depositToGoal($id, $familyId, $userId, $amount, $validated['note'] ?? null);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'goal_deposited',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' menyetor tabungan: Rp '.number_format($amount, 0, ',', '.'),
                data: ['goal' => $goal]
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Setoran tabungan berhasil dicatat!', 'goal' => $goal]);
            }

            return redirect()->route('finances.index')->with('success', 'Setoran tabungan berhasil dicatat!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function storePeriodBudget(Request $request)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:m_categories,id',
            'period' => 'required|date_format:Y-m',
            'budget_amount' => 'required',
            'notes' => 'nullable|string|max:255',
        ]);

        $familyId = Auth::user()->family_id;

        try {
            $budget = $this->financeService->savePeriodBudget($familyId, $validated);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'budget_updated',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' memperbarui anggaran periode '.$validated['period'].'.'
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Anggaran periode berhasil disimpan!', 'budget' => $budget]);
            }

            return redirect()->route('finances.index', ['period' => $validated['period']])->with('success', 'Anggaran periode berhasil disimpan!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }

    public function destroyPeriodBudget(Request $request, int $category)
    {
        if (! Auth::user()->canViewAllFinances()) {
            abort(403, 'Akses terbatas.');
        }

        $period = (string) ($request->input('period') ?: now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = now()->format('Y-m');
        }

        $familyId = Auth::user()->family_id;

        try {
            $categoryName = $this->financeService->deletePeriodBudget($familyId, $category, $period);

            broadcast(new FinanceEvent(
                familyId: (string) $familyId,
                action: 'budget_updated',
                userId: (int) Auth::id(),
                userName: (string) Auth::user()->name,
                message: Auth::user()->name.' menghapus anggaran kategori '.$categoryName.' periode '.$period.'.'
            ))->toOthers();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Batas anggaran kategori berhasil dihapus dan digabungkan ke kategori Lain-lain!',
                ]);
            }

            return redirect()->route('finances.index', ['period' => $period])
                ->with('success', 'Batas anggaran kategori berhasil dihapus dan digabungkan ke kategori Lain-lain!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('finances.index')->with('error', $e->getMessage());
        }
    }
}
