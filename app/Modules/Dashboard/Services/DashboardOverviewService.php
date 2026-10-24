<?php

namespace App\Modules\Dashboard\Services;

use App\Models\Family;
use App\Models\Note;
use App\Models\TimelineMoment;
use App\Models\User;
use App\Modules\Calendar\Repositories\EventRepository;
use App\Modules\Chore\Repositories\ChoreRepository;
use App\Modules\Finance\Repositories\FinanceRepository;
use App\Modules\Finance\Services\FinanceService;
use App\Modules\Subscription\Repositories\PaymentRepository;
use App\Modules\Timeline\Repositories\TimelineRepository;
use Carbon\Carbon;

class DashboardOverviewService
{
    public function __construct(
        protected ChoreRepository $choreRepo,
        protected FinanceService $financeService,
        protected FinanceRepository $financeRepo,
        protected EventRepository $eventRepo,
        protected TimelineRepository $timelineRepo,
        protected PaymentRepository $paymentRepo
    ) {}

    public function getData(Family $family, User $currentUser, string $tab = 'overview', int $page = 1): array
    {
        $familyId = $family->id;
        $page = max(1, $page);

        $chorePage = ($tab === 'chores') ? $page : 1;
        $financePage = ($tab === 'finance') ? $page : 1;
        $eventPage = ($tab === 'calendar') ? $page : 1;
        $timelinePage = ($tab === 'timeline') ? $page : 1;

        $members = $family->users()->orderBy('points', 'desc')->get();
        $allChores = $this->choreRepo->getFamilyChores($familyId);
        $chores = $this->choreRepo->getPaginatedFamilyChores($familyId, 5, 'page', $chorePage);
        $financeUserId = $currentUser->canViewAllFinances() ? null : $currentUser->id;
        $financeData = $this->financeService->calculateOverview($familyId, 5, 'page', $financePage, null, $financeUserId);
        $categories = $this->financeRepo->getCategories($familyId);
        $events = $this->eventRepo->getFamilyEvents($familyId);
        $paginatedEvents = $this->eventRepo->getPaginatedFamilyEvents($familyId, 5, 'page', $eventPage);
        $moments = $this->timelineRepo->getPaginatedFamilyMoments($familyId, 4, 'page', $timelinePage);
        $invoices = $this->paymentRepo->getFamilyInvoices($familyId);

        $subscription = $family->activeSubscription()->with('plan')->first();
        $isPro = $subscription && $subscription->plan && $subscription->plan->isPro() && $subscription->expires_at->isFuture();

        // 1. Chores analytics
        $choresTotal = $allChores->count();
        $choresCompleted = $allChores->where('status', 'completed')->count();
        $choresPending = $allChores->where('status', 'pending')->count();
        $choreCompletionRate = $choresTotal > 0 ? (int) round(($choresCompleted / $choresTotal) * 100) : 0;

        // 2. Finance analytics & cash flow chart data
        $allFinances = $this->financeRepo->getFamilyTransactions($familyId, 100, $financeUserId);
        $incomeExpense = $this->financeRepo->getFamilyIncomeExpense($familyId, $financeUserId);
        $totalIncome = $incomeExpense['income'];
        $totalExpense = $incomeExpense['expense'];
        $netBalance = $totalIncome - $totalExpense;
        $savingsRatio = $totalIncome > 0 ? (int) max(0, min(100, round(($netBalance / $totalIncome) * 100))) : 0;

        // Cash flow grouped by transaction date (chronological)
        $cashflowGrouped = $allFinances->sortBy('transaction_date')->groupBy(function ($item) {
            return $item->transaction_date ? Carbon::parse($item->transaction_date)->locale('id')->isoFormat('D MMM') : 'N/A';
        });

        // Limit to latest 7 active transaction days to prevent cramped bars & horizontal clutter
        if ($cashflowGrouped->count() > 7) {
            $cashflowGrouped = $cashflowGrouped->slice(-7);
        }

        $cashflowLabels = [];
        $cashflowIncome = [];
        $cashflowExpense = [];

        foreach ($cashflowGrouped as $dateLabel => $items) {
            $cashflowLabels[] = $dateLabel;
            $cashflowIncome[] = (float) $items->where('type', 'income')->sum('amount');
            $cashflowExpense[] = (float) $items->where('type', 'expense')->sum('amount');
        }

        if (empty($cashflowLabels)) {
            $cashflowLabels = ['Belum ada transaksi'];
            $cashflowIncome = [0];
            $cashflowExpense = [0];
        }

        // Category breakdown for expenses
        $expensesByCategory = $allFinances->where('type', 'expense')
            ->groupBy(function ($item) {
                return $item->category ? $item->category->name : 'Lainnya';
            })
            ->map(function ($items, $name) {
                return [
                    'name' => $name,
                    'amount' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        // Limit donut chart to top 5 categories + "Lainnya" to prevent legend overflow
        if ($expensesByCategory->count() > 5) {
            $top5 = $expensesByCategory->take(5);
            $others = $expensesByCategory->slice(5);
            $expensesByCategory = $top5->push([
                'name' => 'Lainnya',
                'amount' => (float) $others->sum('amount'),
                'count' => (int) $others->sum('count'),
            ]);
        }

        // 3. Member productivity & gamification breakdown
        $memberStats = $members->map(function ($member) use ($allChores) {
            $userChores = $allChores->where('assigned_to_user_id', $member->id);

            return [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->role ?? 'Anggota',
                'points' => (int) ($member->points ?? 0),
                'completed_chores' => $userChores->where('status', 'completed')->count(),
                'pending_chores' => $userChores->where('status', 'pending')->count(),
                'total_chores' => $userChores->count(),
            ];
        });

        // 4. Events analytics & upcoming schedule
        $upcomingEvents = $events->filter(function ($ev) {
            return Carbon::parse($ev->start_time)->isFuture();
        })->sortBy('start_time')->values();

        if ($upcomingEvents->isEmpty()) {
            $upcomingEvents = $events->sortByDesc('start_time')->values();
        }

        // 5. Timeline moments
        $allMoments = TimelineMoment::where('family_id', $familyId)->orderBy('event_date', 'desc')->get();
        $latestMoment = $allMoments->first();

        // 6. Sticky & Pinned Notes for Family Fridge
        $pinnedNotes = Note::where('family_id', $familyId)
            ->where('is_pinned', true)
            ->with('user')
            ->latest()
            ->take(4)
            ->get();

        return [
            'family' => $family,
            'user' => $currentUser,
            'members' => $members,
            'pinnedNotes' => $pinnedNotes,
            'allChores' => $allChores,
            'chores' => $chores,
            'finance' => $financeData,
            'categories' => $categories,
            'events' => $events,
            'paginatedEvents' => $paginatedEvents,
            'moments' => $moments,
            'invoices' => $invoices,
            'subscription' => $subscription,
            'isPro' => $isPro,

            // New Rich Analytics Data
            'choresTotal' => $choresTotal,
            'choresCompleted' => $choresCompleted,
            'choresPending' => $choresPending,
            'choreCompletionRate' => $choreCompletionRate,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netBalance' => $netBalance,
            'savingsRatio' => $savingsRatio,
            'cashflowLabels' => $cashflowLabels,
            'cashflowIncome' => $cashflowIncome,
            'cashflowExpense' => $cashflowExpense,
            'expensesByCategory' => $expensesByCategory,
            'memberStats' => $memberStats,
            'upcomingEvents' => $upcomingEvents,
            'allMoments' => $allMoments,
            'latestMoment' => $latestMoment,
        ];
    }
}
