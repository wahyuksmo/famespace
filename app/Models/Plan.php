<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'm_plans';

    protected $fillable = [
        'code',
        'name',
        'price',
        'duration_days',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function isPro(): bool
    {
        return in_array($this->code, ['pro_monthly', 'lifetime', 'pro_lifetime']);
    }

    public function isLifetime(): bool
    {
        return in_array($this->code, ['lifetime', 'pro_lifetime']);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
