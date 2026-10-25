<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with optional default fallback.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::query()->find($key);

        return $setting ? $setting->value : $default;
    }

    /**
     * Set/update a setting value by key.
     */
    public static function set(string $key, ?string $value): static
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
