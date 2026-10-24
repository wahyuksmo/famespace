<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'banner_media_id',
    ];

    protected static function booted(): void
    {
        static::created(function (Family $family) {
            Category::seedDefaultsForFamily($family->id);
        });
    }

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

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(AlbumMedia::class, 'banner_media_id');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->bannerMedia?->file_url;
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

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class, 'family_id');
    }

    public function rewardClaims(): HasMany
    {
        return $this->hasMany(RewardClaim::class, 'family_id');
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class, 'family_id');
    }

    public function albumMedia(): HasMany
    {
        return $this->hasMany(AlbumMedia::class, 'family_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'family_id');
    }

    public function pantryItems(): HasMany
    {
        return $this->hasMany(PantryItem::class, 'family_id');
    }

    public function photoCount(): int
    {
        return $this->albumMedia()->where('media_type', 'photo')->count();
    }

    public function videoCount(): int
    {
        return $this->albumMedia()->where('media_type', 'video')->count();
    }

    public function canUploadPhoto(int $additional = 1): bool
    {
        if ($this->isPro()) {
            return true;
        }

        return ($this->photoCount() + $additional) <= 10;
    }

    public function canUploadVideo(int $additional = 1): bool
    {
        if ($this->isPro()) {
            return true;
        }

        return ($this->videoCount() + $additional) <= 1;
    }

    public function albumQuotaInfo(): array
    {
        $isPro = $this->isPro();
        $photos = $this->photoCount();
        $videos = $this->videoCount();

        return [
            'is_pro' => $isPro,
            'photos_used' => $photos,
            'photos_limit' => $isPro ? null : 10,
            'videos_used' => $videos,
            'videos_limit' => $isPro ? null : 1,
            'can_upload_photo' => $isPro || $photos < 10,
            'can_upload_video' => $isPro || $videos < 1,
        ];
    }
}
