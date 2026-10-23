<?php

namespace App\Modules\Subscription\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Repositories\PaymentRepository;
use App\Modules\Subscription\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subService,
        protected PaymentRepository $paymentRepo
    ) {}

    public function createInvoice(Request $request)
    {
        $familyId = Auth::user()->family_id;
        $userId = Auth::id();
        $planCode = $request->input('plan_code', 'pro_monthly');

        try {
            $invoice = $this->subService->generateInvoice($familyId, $userId, $planCode);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'invoice' => $invoice,
                ]);
            }

            return back()->with('invoice', $invoice);
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function simulatePay(Request $request, string $invoiceNumber)
    {
        try {
            $result = $this->subService->simulatePayment($invoiceNumber);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Langganan Pro berhasil diaktifkan!',
                    'data' => $result,
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Selamat! Seluruh anggota keluarga sekarang menikmati Fitur Pro!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    public function toggleDev(Request $request)
    {
        $familyId = Auth::user()->family_id;
        $planCode = $request->input('plan_code', 'pro_monthly');

        try {
            $status = $this->subService->toggleDevPlan($familyId, $planCode);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'plan' => $status]);
            }

            return redirect()->route('dashboard')->with('success', 'Mode paket dialihkan ke: '.strtoupper($status));
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }
}
