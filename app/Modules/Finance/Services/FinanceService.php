<?php

namespace App\Modules\Finance\Services;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\Finance;
use App\Models\FinanceGoal;
use App\Models\Wkcal;
use App\Modules\Finance\Repositories\FinanceRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinanceService
{
    public function __construct(
        protected FinanceRepository $financeRepo
    ) {}

    public function recordTransaction(string $familyId, int $userId, array $data, ?UploadedFile $receipt = null): Finance
    {
        return DB::transaction(function () use ($familyId, $userId, $data, $receipt) {
            if (! empty($data['category_id']) && ! is_numeric($data['category_id'])) {
                $cat = Category::firstOrCreate([
                    'family_id' => $familyId,
                    'name' => trim($data['category_id']),
                ], [
                    'type' => $data['type'] ?? 'expense',
                    'icon' => '🏷️',
                    'color' => '#2F5233',
                ]);
                $data['category_id'] = $cat->id;
            }

            $receiptPath = null;
            if ($receipt && $receipt->isValid()) {
                $receiptPath = $receipt->store('receipts', 'public');
            }

            $amount = (float) str_replace(['.', ','], ['', '.'], (string) $data['amount']);
            $recordingUserId = ! empty($data['user_id']) ? (int) $data['user_id'] : $userId;

            return $this->financeRepo->create([
                'family_id' => $familyId,
                'user_id' => $recordingUserId,
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'amount' => $amount,
                'description' => $data['description'],
                'receipt_path' => $receiptPath,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
            ]);
        });
    }

    public function updateTransaction(int $id, string $familyId, array $data, ?UploadedFile $receipt = null, ?int $userId = null): Finance
    {
        return DB::transaction(function () use ($id, $familyId, $data, $receipt, $userId) {
            $finance = $this->financeRepo->findInFamily($id, $familyId, $userId);
            if (! $finance) {
                throw new Exception('Transaksi kas tidak ditemukan atau Anda tidak memiliki akses.');
            }

            if (! empty($data['category_id']) && ! is_numeric($data['category_id'])) {
                $cat = Category::firstOrCreate([
                    'family_id' => $familyId,
                    'name' => trim($data['category_id']),
                ], [
                    'type' => $data['type'] ?? 'expense',
                    'icon' => '🏷️',
                    'color' => '#2F5233',
                ]);
                $data['category_id'] = $cat->id;
            }

            $updateData = [
                'type' => $data['type'] ?? $finance->type,
                'description' => $data['description'] ?? $finance->description,
                'category_id' => $data['category_id'] ?? $finance->category_id,
                'transaction_date' => $data['transaction_date'] ?? $finance->transaction_date,
            ];

            if (! empty($data['user_id'])) {
                $updateData['user_id'] = (int) $data['user_id'];
            }

            if (isset($data['amount'])) {
                $updateData['amount'] = (float) str_replace(['.', ','], ['', '.'], (string) $data['amount']);
            }

            if (! empty($data['remove_receipt']) && $finance->receipt_path) {
                Storage::disk('public')->delete($finance->receipt_path);
                $updateData['receipt_path'] = null;
            }

            if ($receipt && $receipt->isValid()) {
                if ($finance->receipt_path) {
                    Storage::disk('public')->delete($finance->receipt_path);
                }
                $updateData['receipt_path'] = $receipt->store('receipts', 'public');
            }

            return $this->financeRepo->update($finance, $updateData);
        });
    }

    public function deleteTransaction(int $id, string $familyId, ?int $userId = null): void
    {
        DB::transaction(function () use ($id, $familyId, $userId) {
            $finance = $this->financeRepo->findInFamily($id, $familyId, $userId);
            if (! $finance) {
                throw new Exception('Transaksi kas tidak ditemukan atau Anda tidak memiliki akses.');
            }

            if ($finance->receipt_path) {
                Storage::disk('public')->delete($finance->receipt_path);
            }

            $this->financeRepo->delete($finance);
        });
    }

    public function calculateOverview(string $familyId, int $perPage = 5, string $pageName = 'page', ?int $page = null, ?string $period = null, ?int $userId = null, ?string $type = null): array
    {
        $resolvedType = $type ?? ($userId !== null ? 'expense' : null);
        $all = $this->financeRepo->getFamilyTransactions($familyId, 100, $userId, $resolvedType);
        $paginated = $this->financeRepo->getPaginatedFamilyTransactions($familyId, $perPage, $pageName, $page, $userId, $resolvedType);

        $wkcalPeriods = Wkcal::query()
            ->select('calendar_year', 'calendar_month')
            ->distinct()
            ->orderByDesc('calendar_year')
            ->orderByDesc('calendar_month')
            ->get();

        $currentPeriod = now()->format('Y-m');

        if ($period && preg_match('/^\d{4}-\d{2}$/', $period)) {
            $selectedPeriod = $period;
        } elseif ($wkcalPeriods->isNotEmpty()) {
            $hasCurrent = $wkcalPeriods->contains(function ($row) use ($currentPeriod) {
                return sprintf('%04d-%02d', $row->calendar_year, $row->calendar_month) === $currentPeriod;
            });
            if ($hasCurrent) {
                $selectedPeriod = $currentPeriod;
            } else {
                $first = $wkcalPeriods->first();
                $selectedPeriod = sprintf('%04d-%02d', $first->calendar_year, $first->calendar_month);
            }
        } else {
            $selectedPeriod = $currentPeriod;
        }

        $selectedDate = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();

        $monthlyExpenses = $this->financeRepo->getMonthlyExpensesByCategory($familyId, $selectedPeriod);
        $historicalBudgets = $this->financeRepo->getCategoryBudgetsForPeriod($familyId, $selectedPeriod)
            ->keyBy('category_id');

        $incomeExpense = $this->financeRepo->getFamilyIncomeExpense($familyId, $userId);
        $income = $incomeExpense['income'];
        $expense = $incomeExpense['expense'];
        $saving = $incomeExpense['saving'] ?? 0;
        $balance = $income - $expense - $saving;

        $categories = $this->financeRepo->getCategories($familyId);

        $categoryBudgets = $categories->where('type', 'expense')->map(function ($cat) use ($monthlyExpenses, $historicalBudgets) {
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
                'icon' => $cat->icon,
                'color' => $cat->color,
                'monthly_budget' => $budget,
                'has_custom_period_budget' => $hasCustomPeriodBudget,
                'notes' => $hasCustomPeriodBudget ? $historicalBudgets[$cat->id]->notes : null,
                'spent' => $spent,
                'remaining' => $remaining,
                'pct' => $pct,
                'is_over_budget' => $isOver,
                'is_custom' => ! is_null($cat->family_id),
            ];
        })->values();

        if ($wkcalPeriods->isNotEmpty()) {
            $availablePeriods = $wkcalPeriods->map(function ($row) use ($selectedPeriod, $currentPeriod) {
                $pStr = sprintf('%04d-%02d', $row->calendar_year, $row->calendar_month);
                $pDate = Carbon::createFromDate($row->calendar_year, $row->calendar_month, 1);

                return [
                    'period' => $pStr,
                    'label' => $pDate->locale('id')->isoFormat('MMMM Y'),
                    'is_selected' => $pStr === $selectedPeriod,
                    'is_current' => $pStr === $currentPeriod,
                ];
            })->values()->all();
        } else {
            $availablePeriods = [
                [
                    'period' => $currentPeriod,
                    'label' => now()->locale('id')->isoFormat('MMMM Y'),
                    'is_selected' => true,
                    'is_current' => true,
                ],
            ];
        }

        if (! collect($availablePeriods)->contains('period', $selectedPeriod)) {
            $customDate = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
            array_unshift($availablePeriods, [
                'period' => $selectedPeriod,
                'label' => $customDate->locale('id')->isoFormat('MMMM Y'),
                'is_selected' => true,
                'is_current' => $selectedPeriod === $currentPeriod,
            ]);
        }

        $goals = $this->financeRepo->getGoals($familyId)->map(function ($goal) {
            $target = (float) $goal->target_amount;
            $current = (float) $goal->current_amount;
            $pct = $target > 0 ? min(100, (int) round(($current / $target) * 100)) : 0;

            return [
                'id' => $goal->id,
                'title' => $goal->title,
                'icon' => $goal->icon ?? '🎯',
                'color' => $goal->color ?? '#2F5233',
                'target_amount' => $target,
                'current_amount' => $current,
                'target_date' => $goal->target_date ? $goal->target_date->format('Y-m-d') : null,
                'formatted_target_date' => $goal->target_date ? $goal->target_date->format('d M Y') : null,
                'note' => $goal->note,
                'status' => $goal->status,
                'pct' => $pct,
            ];
        });

        return [
            'income' => $income,
            'expense' => $expense,
            'saving' => $saving,
            'balance' => $balance,
            'transactions' => $paginated,
            'recent_transactions' => $all->take(3),
            'category_budgets' => $categoryBudgets,
            'goals' => $goals,
            'selected_period' => $selectedPeriod,
            'selected_period_label' => $selectedDate->locale('id')->isoFormat('MMMM Y'),
            'available_periods' => $availablePeriods,
        ];
    }

    public function savePeriodBudget(string $familyId, array $data): CategoryBudget
    {
        $amount = (float) str_replace(['.', ','], ['', '.'], (string) ($data['budget_amount'] ?? 0));
        $categoryId = (int) $data['category_id'];
        $period = trim($data['period']);
        $notes = ! empty($data['notes']) ? trim($data['notes']) : null;

        $cat = $this->financeRepo->findCategoryInFamily($categoryId, $familyId);
        if (! $cat) {
            throw new Exception('Kategori tidak ditemukan.');
        }

        return $this->financeRepo->setCategoryBudgetForPeriod($familyId, $categoryId, $period, $amount, $notes);
    }

    public function saveCategory(string $familyId, array $data, ?int $id = null): Category
    {
        $monthlyBudget = isset($data['monthly_budget'])
            ? (float) str_replace(['.', ','], ['', '.'], (string) $data['monthly_budget'])
            : 0.00;

        $payload = [
            'name' => trim($data['name']),
            'type' => $data['type'] ?? 'expense',
            'icon' => $data['icon'] ?? '🏷️',
            'color' => $data['color'] ?? '#2F5233',
            'monthly_budget' => $monthlyBudget,
        ];

        if ($id) {
            $category = $this->financeRepo->findCategoryInFamily($id, $familyId);
            if (! $category) {
                throw new Exception('Kategori tidak ditemukan.');
            }

            return $this->financeRepo->updateCategory($category, $payload);
        }

        $payload['family_id'] = $familyId;

        return $this->financeRepo->createCategory($payload);
    }

    public function deleteCategory(int $id, string $familyId): void
    {
        $category = $this->financeRepo->findCategoryInFamily($id, $familyId);
        if (! $category) {
            throw new Exception('Kategori tidak ditemukan.');
        }

        if (is_null($category->family_id)) {
            throw new Exception('Kategori bawaan sistem tidak dapat dihapus.');
        }

        $this->financeRepo->deleteCategory($category);
    }

    public function saveGoal(string $familyId, array $data, ?int $id = null): FinanceGoal
    {
        $targetAmount = (float) str_replace(['.', ','], ['', '.'], (string) ($data['target_amount'] ?? 0));
        $currentAmount = isset($data['current_amount'])
            ? (float) str_replace(['.', ','], ['', '.'], (string) $data['current_amount'])
            : 0.00;

        $payload = [
            'title' => trim($data['title']),
            'target_amount' => $targetAmount,
            'icon' => $data['icon'] ?? '🎯',
            'color' => $data['color'] ?? '#2F5233',
            'note' => $data['note'] ?? null,
            'target_date' => ! empty($data['target_date']) ? $data['target_date'] : null,
            'status' => $data['status'] ?? 'active',
        ];

        if ($id) {
            $goal = $this->financeRepo->findGoalInFamily($id, $familyId);
            if (! $goal) {
                throw new Exception('Target tabungan tidak ditemukan.');
            }

            if (isset($data['current_amount'])) {
                $payload['current_amount'] = $currentAmount;
            }

            return $this->financeRepo->updateGoal($goal, $payload);
        }

        $payload['family_id'] = $familyId;
        $payload['current_amount'] = $currentAmount;

        return $this->financeRepo->createGoal($payload);
    }

    public function deleteGoal(int $id, string $familyId): void
    {
        $goal = $this->financeRepo->findGoalInFamily($id, $familyId);
        if (! $goal) {
            throw new Exception('Target tabungan tidak ditemukan.');
        }

        $this->financeRepo->deleteGoal($goal);
    }

    public function depositToGoal(int $goalId, string $familyId, int $userId, float $amount, ?string $note = null): FinanceGoal
    {
        return DB::transaction(function () use ($goalId, $familyId, $userId, $amount, $note) {
            $goal = $this->financeRepo->findGoalInFamily($goalId, $familyId);
            if (! $goal) {
                throw new Exception('Target tabungan tidak ditemukan.');
            }

            $newCurrent = (float) $goal->current_amount + $amount;
            $newStatus = $newCurrent >= (float) $goal->target_amount ? 'completed' : $goal->status;

            $this->financeRepo->updateGoal($goal, [
                'current_amount' => $newCurrent,
                'status' => $newStatus,
            ]);

            // Catat alokasi tabungan di kas: mengurangi kas operasional tapi bukan pengeluaran
            $cat = Category::firstOrCreate([
                'family_id' => $familyId,
                'name' => 'Tabungan & Impian',
            ], [
                'type' => 'saving',
                'icon' => '🐷',
                'color' => '#10B981',
                'monthly_budget' => 0,
            ]);

            $this->financeRepo->create([
                'family_id' => $familyId,
                'user_id' => $userId,
                'category_id' => $cat->id,
                'type' => 'saving',
                'amount' => $amount,
                'description' => "Nabung Target: {$goal->title}".($note ? " - {$note}" : ''),
                'transaction_date' => now()->toDateString(),
            ]);

            return $goal;
        });
    }

    public function getFinancialReport(string $familyId, ?string $mode = 'monthly', ?string $period = null, ?int $year = null, ?string $startMonth = null, ?string $endMonth = null): array
    {
        $mode = $mode ?: 'monthly';

        if ($mode === 'yearly') {
            $year = $year ?: (int) now()->format('Y');
            $startDate = "{$year}-01-01";
            $endDate = "{$year}-12-31";
            $periodLabel = "Tahun {$year}";
        } elseif ($mode === 'range') {
            $startMonth = ($startMonth && preg_match('/^\d{4}-\d{2}$/', $startMonth)) ? $startMonth : now()->startOfYear()->format('Y-m');
            $endMonth = ($endMonth && preg_match('/^\d{4}-\d{2}$/', $endMonth)) ? $endMonth : now()->format('Y-m');

            if ($startMonth > $endMonth) {
                [$startMonth, $endMonth] = [$endMonth, $startMonth];
            }

            $startDate = Carbon::createFromFormat('Y-m', $startMonth)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromFormat('Y-m', $endMonth)->endOfMonth()->toDateString();
            $periodLabel = Carbon::parse($startDate)->locale('id')->isoFormat('MMMM Y').' — '.Carbon::parse($endDate)->locale('id')->isoFormat('MMMM Y');
        } else {
            $period = ($period && preg_match('/^\d{4}-\d{2}$/', $period)) ? $period : now()->format('Y-m');
            $date = Carbon::createFromFormat('Y-m', $period);
            $startDate = $date->copy()->startOfMonth()->toDateString();
            $endDate = $date->copy()->endOfMonth()->toDateString();
            $periodLabel = $date->locale('id')->isoFormat('MMMM Y');
        }

        $report = $this->financeRepo->getReportData($familyId, $startDate, $endDate);
        $report['mode'] = $mode;
        $report['period_label'] = $periodLabel;
        $report['selected_period'] = $period ?? now()->format('Y-m');
        $report['selected_year'] = $year ?? (int) now()->format('Y');
        $report['start_month'] = $startMonth ?? now()->startOfYear()->format('Y-m');
        $report['end_month'] = $endMonth ?? now()->format('Y-m');

        return $report;
    }
}
