<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineMoment extends Model
{
    use HasFactory;

    protected $table = 't_timeline_moments';

    protected $fillable = [
        'family_id',
        'user_id',
        'title',
        'story',
        'location_name',
        'latitude',
        'longitude',
        'image_path',
        'event_date',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
