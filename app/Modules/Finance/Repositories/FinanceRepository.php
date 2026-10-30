<?php

namespace App\Modules\Finance\Repositories;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\Finance;
use App\Models\FinanceGoal;
use App\Modules\Finance\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FinanceRepository
{
    public function getFamilyTransactions(string $familyId, int $limit = 50, ?int $userId = null, ?string $type = null): Collection
    {
        $query = Finance::with(['user', 'category'])
            ->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPaginatedFamilyTransactions(string $familyId, int $perPage = 5, string $pageName = 'page', ?int $page = null, ?int $userId = null, ?string $type = null): LengthAwarePaginator
    {
        $query = Finance::with(['user', 'category'])
            ->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], $pageName, $page);
    }

    public function getDataTablesTransactions(string $familyId, int $start = 0, int $length = 10, ?string $search = null, ?string $type = null, ?int $userId = null): array
    {
        $query = Finance::with(['user', 'category'])
            ->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if (! empty($type) && in_array($type, ['income', 'expense', 'saving'])) {
            $query->where('type', $type);
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                    ->orWhere('amount', 'like', "%{$term}%")
                    ->orWhereHas('category', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('user', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();

        $data = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function getFamilyIncomeExpense(string $familyId, ?int $userId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = DB::table('t_finances')->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($startDate !== null && $endDate !== null) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $totals = $query->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
            COALESCE(SUM(CASE WHEN type = 'saving' THEN amount ELSE 0 END), 0) as total_saving
        ")->first();

        return [
            'income' => (float) ($totals->total_income ?? 0),
            'expense' => (float) ($totals->total_expense ?? 0),
            'saving' => (float) ($totals->total_saving ?? 0),
        ];
    }

    public function findInFamily(int $id, string $familyId, ?int $userId = null): ?Finance
    {
        $query = Finance::with(['user', 'category'])
            ->where('id', $id)
            ->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    public function getCategories(string $familyId): Collection
    {
        return Category::where(function ($q) use ($familyId) {
            $q->where('family_id', $familyId)->orWhereNull('family_id');
        })->whereIn('type', ['income', 'expense', 'saving'])->orderBy('name', 'asc')->get();
    }

    public function findCategoryInFamily(int $id, string $familyId): ?Category
    {
        return Category::where('id', $id)
            ->where(function ($q) use ($familyId) {
                $q->where('family_id', $familyId)->orWhereNull('family_id');
            })
            ->first();
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function deleteCategory(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function getGoals(string $familyId): Collection
    {
        return FinanceGoal::where('family_id', $familyId)
            ->orderBy('status', 'asc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findGoalInFamily(int $id, string $familyId): ?FinanceGoal
    {
        return FinanceGoal::where('id', $id)
            ->where('family_id', $familyId)
            ->first();
    }

    public function createGoal(array $data): FinanceGoal
    {
        return FinanceGoal::create($data);
    }

    public function updateGoal(FinanceGoal $goal, array $data): FinanceGoal
    {
        $goal->update($data);

        return $goal;
    }

    public function deleteGoal(FinanceGoal $goal): bool
    {
        return (bool) $goal->delete();
    }

    public function getCategoryBudgetsForPeriod(string $familyId, string $period): Collection
    {
        return CategoryBudget::with('category')
            ->where('family_id', $familyId)
            ->where('period', $period)
            ->get();
    }

    public function setCategoryBudgetForPeriod(string $familyId, int $categoryId, string $period, float $amount, ?string $notes = null): CategoryBudget
    {
        return CategoryBudget::updateOrCreate([
            'family_id' => $familyId,
            'category_id' => $categoryId,
            'period' => $period,
        ], [
            'budget_amount' => $amount,
            'notes' => $notes,
        ]);
    }

    public function deleteCategoryBudgetForPeriod(string $familyId, int $categoryId, string $period): bool
    {
        return (bool) CategoryBudget::where('family_id', $familyId)
            ->where('category_id', $categoryId)
            ->where('period', $period)
            ->delete();
    }

    public function getMonthlyExpensesByCategory(string $familyId, Carbon|string|null $periodOrStart = null, ?string $endDate = null): \Illuminate\Support\Collection
    {
        if ($periodOrStart !== null && $endDate !== null) {
            $start = is_string($periodOrStart) ? $periodOrStart : $periodOrStart->toDateString();
            $end = $endDate;
        } elseif (is_string($periodOrStart) && preg_match('/^\d{4}-\d{2}$/', $periodOrStart)) {
            $date = Carbon::createFromFormat('Y-m', $periodOrStart)->startOfMonth();
            $start = $date->copy()->startOfMonth()->toDateString();
            $end = $date->copy()->endOfMonth()->toDateString();
        } elseif ($periodOrStart instanceof Carbon) {
            $start = $periodOrStart->copy()->startOfMonth()->toDateString();
            $end = $periodOrStart->copy()->endOfMonth()->toDateString();
        } else {
            $date = Carbon::now();
            $start = $date->copy()->startOfMonth()->toDateString();
            $end = $date->copy()->endOfMonth()->toDateString();
        }

        return DB::table('t_finances')
            ->where('family_id', $familyId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category_id')
            ->pluck('total_spent', 'category_id');
    }

    public function getDataTablesBudgets(string $familyId, string $period, int $start = 0, int $length = 10, ?string $search = null, ?string $status = null, int $cutOffDay = 1): array
    {
        $query = Category::where(function ($q) use ($familyId) {
            $q->where('family_id', $familyId)->orWhereNull('family_id');
        })->where('type', 'expense');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where('name', 'like', "%{$term}%");
        }

        $allCategories = $query->orderBy('name', 'asc')->get();
        $cycle = FinanceService::resolveCycleRange($period, $cutOffDay);
        $monthlyExpenses = $this->getMonthlyExpensesByCategory($familyId, $cycle['start_date'], $cycle['end_date']);
        $historicalBudgets = $this->getCategoryBudgetsForPeriod($familyId, $period)->keyBy('category_id');

        $mapped = $allCategories->map(function ($cat) use ($monthlyExpenses, $historicalBudgets) {
            $spent = (float) ($monthlyExpenses[$cat->id] ?? 0);
            $hasCustomPeriodBudget = isset($historicalBudgets[$cat->id]);
            $budget = $hasCustomPeriodBudget
                ? (float) $historicalBudgets[$cat->id]->budget_amount
                : (float) $cat->monthly_budget;

            $remaining = max(0, $budget - $spent);
            $pct = $budget > 0 ? min(100, (int) round(($spent / $budget) * 100)) : 0;
            $isOver = $budget > 0 && $spent > $budget;

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'icon' => $cat->icon ?: '🏷️',
                'color' => $cat->color,
                'monthly_budget' => $budget,
                'formatted_budget' => $budget > 0 ? 'Rp '.number_format($budget, 0, ',', '.') : '—',
                'has_budget' => $budget > 0,
                'has_custom_period_budget' => $hasCustomPeriodBudget,
                'notes' => $hasCustomPeriodBudget ? $historicalBudgets[$cat->id]->notes : null,
                'spent' => $spent,
                'formatted_spent' => 'Rp '.number_format($spent, 0, ',', '.'),
                'remaining' => $remaining,
                'formatted_remaining' => 'Rp '.number_format($remaining, 0, ',', '.'),
                'pct' => $pct,
                'is_over_budget' => $isOver,
                'over_amount' => $isOver ? ($spent - $budget) : 0,
                'formatted_over' => 'Rp '.number_format($spent - $budget, 0, ',', '.'),
                'is_custom' => ! is_null($cat->family_id),
            ];
        });

        if (! empty($status) && in_array($status, ['over', 'on_track', 'no_budget'])) {
            $mapped = $mapped->filter(function ($item) use ($status) {
                if ($status === 'over') {
                    return $item['is_over_budget'];
                }
                if ($status === 'on_track') {
                    return $item['has_budget'] && ! $item['is_over_budget'];
                }
                if ($status === 'no_budget') {
                    return ! $item['has_budget'];
                }

                return true;
            })->values();
        }

        $recordsTotal = Category::where(function ($q) use ($familyId) {
            $q->where('family_id', $familyId)->orWhereNull('family_id');
        })->where('type', 'expense')->count();

        $recordsFiltered = $mapped->count();
        $pagedData = $mapped->slice($start, $length)->values();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $pagedData,
        ];
    }

    public function getDataTablesCategories(string $familyId, int $start = 0, int $length = 10, ?string $search = null, ?string $type = null): array
    {
        $query = Category::where(function ($q) use ($familyId) {
            $q->where('family_id', $familyId)->orWhereNull('family_id');
        });

        if (! empty($type) && in_array($type, ['income', 'expense', 'saving'])) {
            $query->where('type', $type);
        } else {
            $query->whereIn('type', ['income', 'expense']);
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where('name', 'like', "%{$term}%");
        }

        $recordsFiltered = (clone $query)->count();

        $data = $query->orderByRaw('CASE WHEN family_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('name', 'asc')
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function ($cat) {
                $budget = (float) ($cat->monthly_budget ?? 0);

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'type' => $cat->type,
                    'type_label' => $cat->type === 'expense' ? 'Pengeluaran' : 'Pemasukan',
                    'icon' => $cat->icon ?: '🏷️',
                    'color' => $cat->color,
                    'monthly_budget' => $budget,
                    'formatted_budget' => $budget > 0 ? 'Rp '.number_format($budget, 0, ',', '.').' / bln' : '—',
                    'is_custom' => ! is_null($cat->family_id),
                    'destroy_url' => route('finances.categories.destroy', $cat->id),
                ];
            });

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function getReportData(string $familyId, string $startDate, string $endDate): array
    {
        $totals = DB::table('t_finances')
            ->where('family_id', $familyId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
                COALESCE(SUM(CASE WHEN type = 'saving' THEN amount ELSE 0 END), 0) as total_saving
            ")
            ->first();

        $totalIncome = (float) ($totals->total_income ?? 0);
        $totalExpense = (float) ($totals->total_expense ?? 0);
        $totalSaving = (float) ($totals->total_saving ?? 0);
        $netBalance = $totalIncome - $totalExpense - $totalSaving;

        $categoryExpenses = DB::table('t_finances')
            ->leftJoin('m_categories', 't_finances.category_id', '=', 'm_categories.id')
            ->where('t_finances.family_id', $familyId)
            ->where('t_finances.type', 'expense')
            ->whereBetween('t_finances.transaction_date', [$startDate, $endDate])
            ->selectRaw("
                t_finances.category_id,
                COALESCE(m_categories.name, 'Tanpa Kategori') as category_name,
                COALESCE(m_categories.icon, '🏷️') as category_icon,
                COALESCE(m_categories.color, '#2F5233') as category_color,
                SUM(t_finances.amount) as total_amount,
                COUNT(t_finances.id) as transaction_count
            ")
            ->groupBy('t_finances.category_id', 'm_categories.name', 'm_categories.icon', 'm_categories.color')
            ->orderByDesc('total_amount')
            ->get();

        $categoriesList = $categoryExpenses->map(function ($cat) use ($totalIncome, $totalExpense) {
            $amount = (float) $cat->total_amount;
            $pctOfIncome = $totalIncome > 0 ? round(($amount / $totalIncome) * 100, 1) : 0;
            $pctOfExpense = $totalExpense > 0 ? round(($amount / $totalExpense) * 100, 1) : 0;

            return [
                'id' => $cat->category_id,
                'name' => $cat->category_name,
                'icon' => $cat->category_icon,
                'color' => $cat->category_color,
                'amount' => $amount,
                'formatted_amount' => 'Rp '.number_format($amount, 0, ',', '.'),
                'transaction_count' => (int) $cat->transaction_count,
                'pct_of_income' => $pctOfIncome,
                'pct_of_expense' => $pctOfExpense,
            ];
        });

        $pctSavingOfIncome = $totalIncome > 0 ? round(($totalSaving / $totalIncome) * 100, 1) : 0;
        $pctExpenseOfIncome = $totalIncome > 0 ? round(($totalExpense / $totalIncome) * 100, 1) : 0;
        $pctRemainingOfIncome = $totalIncome > 0 ? max(0, round(($netBalance / $totalIncome) * 100, 1)) : 0;

        $topCategory = $categoriesList->first();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_income' => $totalIncome,
            'formatted_total_income' => 'Rp '.number_format($totalIncome, 0, ',', '.'),
            'total_expense' => $totalExpense,
            'formatted_total_expense' => 'Rp '.number_format($totalExpense, 0, ',', '.'),
            'total_saving' => $totalSaving,
            'formatted_total_saving' => 'Rp '.number_format($totalSaving, 0, ',', '.'),
            'net_balance' => $netBalance,
            'formatted_net_balance' => 'Rp '.number_format($netBalance, 0, ',', '.'),
            'pct_saving_of_income' => $pctSavingOfIncome,
            'pct_expense_of_income' => $pctExpenseOfIncome,
            'pct_remaining_of_income' => $pctRemainingOfIncome,
            'categories' => $categoriesList,
            'top_category' => $topCategory,
        ];
    }

    public function create(array $data): Finance
    {
        return Finance::create($data);
    }

    public function update(Finance $finance, array $data): Finance
    {
        $finance->update($data);

        return $finance;
    }

    public function delete(Finance $finance): bool
    {
        return (bool) $finance->delete();
    }
}
