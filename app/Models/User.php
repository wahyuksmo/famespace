<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'm_users';

    protected $fillable = [
        'family_id',
        'name',
        'email',
        'password',
        'role',
        'can_view_all_finances',
        'points',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points' => 'integer',
            'can_view_all_finances' => 'boolean',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $val = $this->attributes['avatar_url'] ?? null;
        if (! $val) {
            return null;
        }

        return str_starts_with($val, 'http') ? $val : asset('storage/'.$val);
    }

    public function canViewAllFinances(): bool
    {
        return strtolower((string) $this->role) === 'ayah' || (bool) $this->can_view_all_finances;
    }

    public function isAyah(): bool
    {
        return strtolower((string) $this->role) === 'ayah';
    }

    public function isParent(): bool
    {
        return in_array(strtolower((string) $this->role), ['ayah', 'ibu']);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function assignedChores(): HasMany
    {
        return $this->hasMany(Chore::class, 'assigned_to_user_id');
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class, 'user_id');
    }

    public function moments(): HasMany
    {
        return $this->hasMany(TimelineMoment::class, 'user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by_user_id');
    }

    public function rewardClaims(): HasMany
    {
        return $this->hasMany(RewardClaim::class, 'user_id');
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class, 'user_id');
    }

    public function albumMedia(): HasMany
    {
        return $this->hasMany(AlbumMedia::class, 'user_id');
    }
}
