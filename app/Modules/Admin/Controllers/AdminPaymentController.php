<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Modules\Admin\Services\AdminPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected AdminPaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = SubscriptionPayment::with(['user', 'family', 'plan', 'verifier'])
            ->latest('id');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('family', fn ($f) => $f->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        $stats = [
            'all' => SubscriptionPayment::count(),
            'paid' => SubscriptionPayment::where('status', 'paid')->count(),
            'verified' => SubscriptionPayment::where('status', 'verified')->count(),
            'reverted' => SubscriptionPayment::where('status', 'reverted')->count(),
        ];

        return view('modules.admin.payments.index', compact('payments', 'stats', 'status', 'search'));
    }

    public function verify(Request $request, int $id)
    {
        try {
            $payment = $this->paymentService->verifyPayment($id, Auth::id());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pembayaran {$payment->invoice_number} berhasil diverifikasi valid!",
                    'payment' => $payment,
                ]);
            }

            return back()->with('success', "Pembayaran {$payment->invoice_number} berhasil diverifikasi valid!");
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function revert(Request $request, int $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->paymentService->revertPayment(
                $id,
                Auth::id(),
                $request->input('admin_notes')
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pembayaran {$payment->invoice_number} telah di-revert! Status keluarga kembali ke Free Tier.",
                    'payment' => $payment,
                ]);
            }

            return back()->with('success', "Pembayaran {$payment->invoice_number} telah di-revert! Status keluarga kembali ke Free Tier.");
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}
