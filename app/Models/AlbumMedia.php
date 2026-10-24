<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlbumMedia extends Model
{
    use HasFactory;

    protected $table = 't_album_media';

    protected $fillable = [
        'album_id',
        'family_id',
        'user_id',
        'media_type',
        'file_path',
        'file_name',
        'file_size',
        'caption',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'album_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isPhoto(): bool
    {
        return $this->media_type === 'photo';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function getFileUrlAttribute(): string
    {
        return str_starts_with($this->file_path, 'http')
            ? $this->file_path
            : asset('storage/'.$this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
