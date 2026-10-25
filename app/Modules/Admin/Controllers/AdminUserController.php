<?php

namespace App\Modules\Admin\Controllers;

use App\Events\SubscriptionEvent;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Plan;
use App\Modules\Subscription\Repositories\SubscriptionRepository;
use Illuminate\Http\Request;
use Throwable;

class AdminUserController extends Controller
{
    public function __construct(
        protected SubscriptionRepository $subRepo
    ) {}

    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Family::with(['users', 'subscription.plan'])
            ->withCount('users')
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('invite_code', 'like', "%{$search}%")
                    ->orWhereHas('users', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $families = $query->paginate(15)->withQueryString();
        $plans = Plan::where('is_active', true)->get();

        return view('modules.admin.users.index', compact('families', 'plans', 'search'));
    }

    public function overridePlan(Request $request, string $familyId)
    {
        $request->validate([
            'plan_code' => 'required|string',
        ]);

        $family = Family::findOrFail($familyId);
        $planCode = $request->input('plan_code');

        try {
            if ($planCode === 'free') {
                $this->subRepo->revertToFree($familyId);
                $message = "Paket keluarga {$family->name} berhasil diturunkan ke Free.";
            } else {
                $plan = Plan::where('code', $planCode)->firstOrFail();
                $this->subRepo->activatePro($familyId, $plan->id, $plan->duration_days);
                $message = "Paket keluarga {$family->name} berhasil diatur ke {$plan->name}.";
            }

            broadcast(new SubscriptionEvent(
                familyId: (string) $familyId,
                action: 'plan_override',
                userId: 0,
                userName: 'Super Administrator',
                message: 'Status langganan keluarga telah diperbarui oleh Admin ke: '.strtoupper($planCode),
                data: ['plan' => $planCode, 'is_pro' => in_array($planCode, ['pro_monthly', 'lifetime'])]
            ));

            return back()->with('success', $message);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
