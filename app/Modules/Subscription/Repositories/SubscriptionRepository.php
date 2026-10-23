<?php

namespace App\Modules\Subscription\Repositories;

use App\Models\Plan;
use App\Models\Subscription;

class SubscriptionRepository
{
    public function getFamilySubscription(string $familyId): ?Subscription
    {
        return Subscription::with('plan')
            ->where('family_id', $familyId)
            ->latest('id')
            ->first();
    }

    public function activatePro(string $familyId, int $planId, int $durationDays = 30): Subscription
    {
        $current = Subscription::with('plan')
            ->where('family_id', $familyId)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        $isPaidPlan = $current && $current->plan && $current->plan->isPro();
        $baseDate = ($isPaidPlan && $current->expires_at->isFuture()) ? $current->expires_at : now();
        $expiresAt = $baseDate->copy()->addDays($durationDays);

        return Subscription::create([
            'family_id' => $familyId,
            'plan_id' => $planId,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $expiresAt,
            'auto_renew' => false,
        ]);
    }

    public function revertToFree(string $familyId): void
    {
        $freePlan = Plan::where('code', 'free')->first();
        if ($freePlan) {
            Subscription::create([
                'family_id' => $familyId,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addYears(10),
            ]);
        }
    }
}
