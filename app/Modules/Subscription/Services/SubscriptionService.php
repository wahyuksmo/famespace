<?php

namespace App\Modules\Subscription\Services;

use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Modules\Subscription\Repositories\PaymentRepository;
use App\Modules\Subscription\Repositories\SubscriptionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionService
{
    public function __construct(
        protected SubscriptionRepository $subRepo,
        protected PaymentRepository $paymentRepo
    ) {}

    public function generateInvoice(string $familyId, int $userId, string $planCode = 'pro_monthly'): SubscriptionPayment
    {
        return DB::transaction(function () use ($familyId, $userId, $planCode) {
            $plan = Plan::where('code', $planCode)->first() ?? Plan::where('code', 'pro_monthly')->firstOrFail();

            // Format: INV-YYYYMMDD-XXXX
            $dateStr = now()->format('Ymd');
            $random = strtoupper(Str::random(4));
            $invoiceNumber = "INV-{$dateStr}-{$random}";

            return $this->paymentRepo->createInvoice([
                'invoice_number' => $invoiceNumber,
                'family_id' => $familyId,
                'user_id' => $userId,
                'plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_method' => 'QRIS Statis',
                'status' => 'pending',
            ]);
        });
    }

    public function simulatePayment(string $invoiceNumber, ?string $transferTime = null, ?string $proofPath = null): array
    {
        return DB::transaction(function () use ($invoiceNumber, $transferTime, $proofPath) {
            $invoice = $this->paymentRepo->findByInvoiceNumber($invoiceNumber);
            if (! $invoice) {
                throw new \Exception('Invoice tidak ditemukan.');
            }

            // Activate Pro
            $subscription = $this->subRepo->activatePro(
                $invoice->family_id,
                $invoice->plan_id,
                $invoice->plan->duration_days ?? 30
            );

            // Update payment record
            $invoice->update([
                'subscription_id' => $subscription->id,
                'status' => 'paid',
                'paid_at' => now(),
                'reference_code' => 'QRIS-'.strtoupper(Str::random(8)),
                'transfer_time' => $transferTime ?: now()->format('H:i').' WIB',
                'proof_image' => $proofPath,
            ]);

            return [
                'invoice' => $invoice,
                'subscription' => $subscription,
            ];
        });
    }

    public function toggleDevPlan(string $familyId, string $planCode = 'pro_monthly'): string
    {
        return DB::transaction(function () use ($familyId, $planCode) {
            $current = $this->subRepo->getFamilySubscription($familyId);
            $isPro = $current && $current->plan && $current->plan->isPro() && $current->expires_at->isFuture();

            if ($isPro && $current->plan->code === $planCode) {
                $this->subRepo->revertToFree($familyId);

                return 'free';
            } else {
                $plan = Plan::where('code', $planCode)->first() ?? Plan::where('code', 'pro_monthly')->firstOrFail();
                $this->subRepo->activatePro($familyId, $plan->id, $plan->duration_days);

                return $plan->code;
            }
        });
    }
}
