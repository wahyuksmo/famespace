<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::where('role', '!=', 'super_admin')->count();
        $totalFamilies = Family::count();

        $activeSubscriptions = Subscription::with('plan')
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->get();

        $totalPro = $activeSubscriptions->filter(fn ($s) => $s->plan && $s->plan->code === 'pro_monthly')->count();
        $totalLifetime = $activeSubscriptions->filter(fn ($s) => $s->plan && $s->plan->isLifetime())->count();
        $totalFree = max(0, $totalFamilies - ($totalPro + $totalLifetime));

        $totalRevenue = SubscriptionPayment::whereIn('status', ['paid', 'verified'])->sum('amount');
        $pendingAuditCount = SubscriptionPayment::where('status', 'paid')->count();

        $recentPayments = SubscriptionPayment::with(['user', 'family', 'plan'])
            ->latest('id')
            ->limit(7)
            ->get();

        $recentFamilies = Family::withCount('users')
            ->with(['subscription.plan'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('modules.admin.dashboard', compact(
            'totalUsers',
            'totalFamilies',
            'totalPro',
            'totalLifetime',
            'totalFree',
            'totalRevenue',
            'pendingAuditCount',
            'recentPayments',
            'recentFamilies'
        ));
    }
}
