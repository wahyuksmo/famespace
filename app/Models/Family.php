<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Family extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'm_families';

    protected $fillable = [
        'name',
        'invite_code',
        'monthly_budget',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'monthly_budget' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'family_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'family_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'family_id')
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latestOfMany();
    }

    public function isPro(): bool
    {
        $sub = $this->activeSubscription()->with('plan')->first();

        return (bool) ($sub && $sub->plan && $sub->plan->isPro());
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'family_id');
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class, 'family_id');
    }

    public function chores(): HasMany
    {
        return $this->hasMany(Chore::class, 'family_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'family_id');
    }

    public function timelineMoments(): HasMany
    {
        return $this->hasMany(TimelineMoment::class, 'family_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'family_id');
    }

    public function financeGoals(): HasMany
    {
        return $this->hasMany(FinanceGoal::class, 'family_id');
    }

    public function categoryBudgets(): HasMany
    {
        return $this->hasMany(CategoryBudget::class, 'family_id');
    }
}
