<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    use HasFactory;

    protected $table = 'm_albums';

    protected $fillable = [
        'family_id',
        'user_id',
        'title',
        'description',
        'cover_image_path',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(AlbumMedia::class, 'album_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AlbumMedia::class, 'album_id')->where('media_type', 'photo');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(AlbumMedia::class, 'album_id')->where('media_type', 'video');
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image_path) {
            return str_starts_with($this->cover_image_path, 'http')
                ? $this->cover_image_path
                : asset('storage/'.$this->cover_image_path);
        }

        // fallback to first photo if available
        $firstPhoto = $this->photos()->latest()->first();
        if ($firstPhoto) {
            return $firstPhoto->file_url;
        }

        return null;
    }
}
