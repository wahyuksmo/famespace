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
            return redirect()->route('register');
        }

        $data = $this->overviewService->getData($family, $user, 'overview', 1);

        return view('dashboard.index', $data);
    }
}
