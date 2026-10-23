<?php

namespace App\Modules\Finance\Repositories;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\Finance;
use App\Models\FinanceGoal;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FinanceRepository
{
    public function getFamilyTransactions(string $familyId, int $limit = 50, ?int $userId = null): Collection
    {
        $query = Finance::with(['user', 'category'])
            ->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPaginatedFamilyTransactions(string $familyId, int $perPage = 5, string $pageName = 'page', ?int $page = null, ?int $userId = null): LengthAwarePaginator
    {
        $query = Finance::with(['user', 'category'])
            ->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
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

        if (! empty($type) && in_array($type, ['income', 'expense'])) {
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

    public function getFamilyIncomeExpense(string $familyId, ?int $userId = null): array
    {
        $query = DB::table('t_finances')->where('family_id', $familyId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $totals = $query->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
        ")->first();

        return [
            'income' => (float) ($totals->total_income ?? 0),
            'expense' => (float) ($totals->total_expense ?? 0),
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
        })->orderBy('name', 'asc')->get();
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

    public function getMonthlyExpensesByCategory(string $familyId, Carbon|string|null $period = null): \Illuminate\Support\Collection
    {
        if (is_string($period) && preg_match('/^\d{4}-\d{2}$/', $period)) {
            $date = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        } elseif ($period instanceof Carbon) {
            $date = $period;
        } else {
            $date = Carbon::now();
        }

        $start = $date->copy()->startOfMonth()->toDateString();
        $end = $date->copy()->endOfMonth()->toDateString();

        return DB::table('t_finances')
            ->where('family_id', $familyId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category_id')
            ->pluck('total_spent', 'category_id');
    }

    public function getDataTablesBudgets(string $familyId, string $period, int $start = 0, int $length = 10, ?string $search = null, ?string $status = null): array
    {
        $query = Category::where(function ($q) use ($familyId) {
            $q->where('family_id', $familyId)->orWhereNull('family_id');
        })->where('type', 'expense');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where('name', 'like', "%{$term}%");
        }

        $allCategories = $query->orderBy('name', 'asc')->get();
        $monthlyExpenses = $this->getMonthlyExpensesByCategory($familyId, $period);
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

        if (! empty($type) && in_array($type, ['income', 'expense'])) {
            $query->where('type', $type);
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
