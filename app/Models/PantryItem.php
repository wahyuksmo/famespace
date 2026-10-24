<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PantryItem extends Model
{
    use HasFactory;

    protected $table = 't_pantry_items';

    protected $fillable = [
        'family_id',
        'name',
        'category',
        'quantity',
        'unit',
        'min_quantity',
        'expired_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'min_quantity' => 'decimal:2',
            'expired_at' => 'date',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLowStock(): bool
    {
        return (float) $this->quantity <= (float) $this->min_quantity;
    }

    public function isExpired(): bool
    {
        return $this->expired_at !== null && $this->expired_at->isPast() && ! $this->expired_at->isToday();
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        if ($this->expired_at === null || $this->isExpired()) {
            return false;
        }

        return $this->expired_at->lte(now()->addDays($days));
    }

    public function canDelete(User $user): bool
    {
        return $user->family_id === $this->family_id && $user->isParent();
    }
}
