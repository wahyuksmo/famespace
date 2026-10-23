<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'm_categories';

    protected $fillable = [
        'family_id',
        'name',
        'type',
        'icon',
        'color',
        'monthly_budget',
    ];

    public static function resolveIcon(?string $icon): string
    {
        if (blank($icon)) {
            return '🏷️';
        }

        $map = [
            'wallet' => '💰',
            'shopping-bag' => '🛍️',
            'shopping-cart' => '🛒',
            'bolt' => '⚡',
            'academic-cap' => '🎓',
            'heart' => '💊',
            'truck' => '🚗',
            'film' => '🎬',
            'tag' => '🏷️',
            'piggy-bank' => '🐷',
            'receipt' => '🧾',
            'food' => '🍽️',
            'utensils' => '🍽️',
            'coffee' => '☕',
            'gift' => '🎁',
            'home' => '🏠',
            'car' => '🚗',
            'book' => '📚',
            'briefcase' => '💼',
            'medical' => '🏥',
            'hospital' => '🏥',
        ];

        return $map[$icon] ?? $icon;
    }

    protected function icon(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => self::resolveIcon($value),
            set: fn (?string $value) => self::resolveIcon($value),
        );
    }

    protected function casts(): array
    {
        return [
            'monthly_budget' => 'decimal:2',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class, 'category_id');
    }

    public function categoryBudgets(): HasMany
    {
        return $this->hasMany(CategoryBudget::class, 'category_id');
    }
}
