<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Modules\Admin\Services\AdminPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        $qrisImage = Setting::get('qris_image_path');
        $merchantName = Setting::get('qris_merchant_name', 'FamSpace Official');
        $nmid = Setting::get('qris_nmid', 'ID1020030040050');

        return view('modules.admin.payments.index', compact('payments', 'stats', 'status', 'search', 'qrisImage', 'merchantName', 'nmid'));
    }

    public function updateQris(Request $request)
    {
        $request->validate([
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'merchant_name' => 'nullable|string|max:100',
            'nmid' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('qris_image')) {
            $oldPath = Setting::get('qris_image_path');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('qris_image')->store('qris', 'public');
            Setting::set('qris_image_path', $path);
        }

        if ($request->has('merchant_name')) {
            Setting::set('qris_merchant_name', $request->input('merchant_name'));
        }

        if ($request->has('nmid')) {
            Setting::set('qris_nmid', $request->input('nmid'));
        }

        return back()->with('success', 'Pengaturan QRIS Statis berhasil diperbarui!');
    }

    public function resetQris()
    {
        $oldPath = Setting::get('qris_image_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        Setting::set('qris_image_path', null);

        return back()->with('success', 'Gambar QRIS berhasil dikembalikan ke default SVG.');
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
