<?php

namespace App\Modules\Subscription\Controllers;

use App\Events\SubscriptionEvent;
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

    public function qrisPay(Request $request)
    {
        $request->validate([
            'plan_code' => 'required|string|in:pro_monthly,lifetime',
            'transfer_time' => 'nullable|string|max:50',
            'proof_image' => 'required|image|max:5120',
        ], [
            'proof_image.required' => 'Eits, struk bukti transfernya di-upload dulu dong kak! Biar admin nggak bingung ngecek mutasi 📸',
        ]);

        $familyId = Auth::user()->family_id;
        $userId = Auth::id();
        $planCode = $request->input('plan_code', 'pro_monthly');

        try {
            $invoice = $this->subService->generateInvoice($familyId, $userId, $planCode);

            $proofPath = null;
            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('payment-proofs', 'public');
            }

            $transferTime = $request->input('transfer_time') ?: now()->format('H:i').' WIB';

            $result = $this->subService->simulatePayment($invoice->invoice_number, $transferTime, $proofPath);
            $user = Auth::user();

            broadcast(new SubscriptionEvent(
                familyId: (string) $user->family_id,
                action: 'plan_upgraded',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: 'Selamat! Seluruh anggota keluarga sekarang menikmati Fitur FamKoji Pro! 👑',
                data: ['plan' => 'pro', 'is_pro' => true]
            ));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Horeee! Akun keluarga kamu sekarang resmi jadi PRO! 👑 Admin bakal cek mutasi santai sambil ngopi. Selamat seru-seruan bareng keluarga!',
                    'data' => $result,
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Horeee! Akun keluarga kamu sekarang resmi jadi PRO! 👑 Selamat menikmati semua fiturnya!');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    public function simulatePay(Request $request, string $invoiceNumber)
    {
        try {
            $proofPath = null;
            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('payment-proofs', 'public');
            }

            $transferTime = $request->input('transfer_time');

            $result = $this->subService->simulatePayment($invoiceNumber, $transferTime, $proofPath);
            $user = Auth::user();

            broadcast(new SubscriptionEvent(
                familyId: (string) $user->family_id,
                action: 'plan_upgraded',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: 'Selamat! Seluruh anggota keluarga sekarang menikmati Fitur FamKoji Pro! 👑',
                data: ['plan' => 'pro', 'is_pro' => true]
            ));

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
            $user = Auth::user();

            broadcast(new SubscriptionEvent(
                familyId: (string) $familyId,
                action: 'plan_toggled',
                userId: (int) $user->id,
                userName: (string) $user->name,
                message: 'Status paket langganan keluarga berubah menjadi: '.strtoupper($status),
                data: ['plan' => $status, 'is_pro' => $status === 'pro']
            ));

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
