<?php

namespace App\Modules\Admin\Services;

use App\Events\SubscriptionEvent;
use App\Models\SubscriptionPayment;
use App\Modules\Subscription\Repositories\SubscriptionRepository;
use Illuminate\Support\Facades\DB;

class AdminPaymentService
{
    public function __construct(
        protected SubscriptionRepository $subRepo
    ) {}

    public function verifyPayment(int $paymentId, int $adminUserId): SubscriptionPayment
    {
        return DB::transaction(function () use ($paymentId, $adminUserId) {
            $payment = SubscriptionPayment::findOrFail($paymentId);
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => $adminUserId,
            ]);

            return $payment;
        });
    }

    public function revertPayment(int $paymentId, int $adminUserId, ?string $adminNotes = null): SubscriptionPayment
    {
        return DB::transaction(function () use ($paymentId, $adminUserId, $adminNotes) {
            $payment = SubscriptionPayment::with(['family', 'user'])->findOrFail($paymentId);

            // Revert family subscription to free
            $this->subRepo->revertToFree($payment->family_id);

            // Update payment record
            $payment->update([
                'status' => 'reverted',
                'admin_notes' => $adminNotes ?: 'Transaksi tidak valid / dana tidak ditemukan pada mutasi rekening.',
                'verified_at' => now(),
                'verified_by' => $adminUserId,
            ]);

            // Broadcast real-time downgrade to the family
            broadcast(new SubscriptionEvent(
                familyId: (string) $payment->family_id,
                action: 'plan_reverted',
                userId: (int) $payment->user_id,
                userName: (string) ($payment->user?->name ?? 'Admin'),
                message: 'Status langganan keluarga telah diturunkan ke Free Tier oleh Administrator.',
                data: ['plan' => 'free', 'is_pro' => false]
            ));

            return $payment;
        });
    }
}
