<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'm_rewards';

    protected $fillable = [
        'family_id',
        'title',
        'description',
        'points',
        'icon',
        'stock',
        'created_by_user_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(RewardClaim::class, 'reward_id');
    }

    public function hasStock(): bool
    {
        return is_null($this->stock) || $this->stock > 0;
    }
}
