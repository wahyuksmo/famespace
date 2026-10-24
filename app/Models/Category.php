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

    /**
     * @return array<int, array{name: string, type: string, icon: string, color: string}>
     */
    public static function defaultCategories(): array
    {
        return [
            // Pengeluaran (Expense)
            ['name' => 'Belanja / Kebutuhan Pokok', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#2F5233'],
            ['name' => 'Tagihan', 'type' => 'expense', 'icon' => 'bolt', 'color' => '#E63946'],
            ['name' => 'Servis Kendaraan', 'type' => 'expense', 'icon' => 'car', 'color' => '#457B9D'],
            ['name' => 'Hiburan', 'type' => 'expense', 'icon' => 'film', 'color' => '#F4A261'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'medical', 'color' => '#E76F51'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'academic-cap', 'color' => '#2A9D8F'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'truck', 'color' => '#3A86FF'],
            ['name' => 'Sedekah', 'type' => 'expense', 'icon' => 'gift', 'color' => '#10B981'],
            ['name' => 'Hadiah', 'type' => 'expense', 'icon' => 'gift', 'color' => '#8338EC'],
            ['name' => 'Keluarga', 'type' => 'expense', 'icon' => 'home', 'color' => '#FB8500'],
            ['name' => 'Lain-lain', 'type' => 'expense', 'icon' => 'tag', 'color' => '#6C757D'],

            // Pemasukan (Income)
            ['name' => 'Gaji', 'type' => 'income', 'icon' => 'wallet', 'color' => '#10B981'],
            ['name' => 'Usaha', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#3B82F6'],
            ['name' => 'Bonus', 'type' => 'income', 'icon' => 'gift', 'color' => '#8B5CF6'],
            ['name' => 'Hadiah', 'type' => 'income', 'icon' => 'gift', 'color' => '#EC4899'],
            ['name' => 'Refund/Cashback', 'type' => 'income', 'icon' => 'piggy-bank', 'color' => '#F59E0B'],
            ['name' => 'Komisi', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#14B8A6'],
            ['name' => 'Lain-lain', 'type' => 'income', 'icon' => 'tag', 'color' => '#6B7280'],

            // Linimasa (Timeline)
            ['name' => 'Wisata Alam', 'type' => 'timeline', 'icon' => '🌲', 'color' => '#2E7D32'],
            ['name' => 'Kuliner', 'type' => 'timeline', 'icon' => '🍜', 'color' => '#D84315'],
            ['name' => 'Staycation', 'type' => 'timeline', 'icon' => '🏨', 'color' => '#1565C0'],
            ['name' => 'Silaturahmi & Mudik', 'type' => 'timeline', 'icon' => '🚗', 'color' => '#6A1B9A'],
            ['name' => 'Edukasi & Budaya', 'type' => 'timeline', 'icon' => '🏛️', 'color' => '#00838F'],
            ['name' => 'Hiburan & Rekreasi', 'type' => 'timeline', 'icon' => '🎡', 'color' => '#C2185B'],
            ['name' => 'Lain-lain', 'type' => 'timeline', 'icon' => '📸', 'color' => '#455A64'],
        ];
    }

    public static function seedDefaultsForFamily(string $familyId): void
    {
        foreach (self::defaultCategories() as $item) {
            self::firstOrCreate([
                'family_id' => $familyId,
                'name' => $item['name'],
                'type' => $item['type'],
            ], [
                'icon' => $item['icon'],
                'color' => $item['color'],
            ]);
        }
    }

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

    public function timelineMoments(): HasMany
    {
        return $this->hasMany(TimelineMoment::class, 'category_id');
    }

    public function categoryBudgets(): HasMany
    {
        return $this->hasMany(CategoryBudget::class, 'category_id');
    }
}
