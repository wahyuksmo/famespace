<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardOverviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardOverviewService $overviewService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $family = $user->family;

        if (! $family) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak lagi terdaftar dalam ruang keluarga.',
                ], 403);
            }

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda tidak lagi terdaftar dalam ruang keluarga.']);
        }

        $data = $this->overviewService->getData($family, $user, 'overview', 1);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'pending_chores' => $data['choresPending'],
                    'completion_rate' => $data['choreCompletionRate'],
                    'balance' => $data['currentBalance'],
                    'total_income' => $data['totalIncome'],
                    'total_expense' => $data['totalExpense'],
                    'user_points' => $user->fresh()?->points ?? 0,
                ],
            ]);
        }

        return view('dashboard.index', $data);
    }
}
