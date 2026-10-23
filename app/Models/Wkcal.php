<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wkcal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'm_wkcal';

    protected $primaryKey = 'wkcal_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'calendar_date' => 'date:Y-m-d',
            'calendar_year' => 'integer',
            'calendar_month' => 'integer',
            'day_of_week' => 'integer',
            'start_time' => 'string',
            'end_time' => 'string',
        ];
    }

    public function getIdAttribute(): string
    {
        return (string) $this->wkcal_id;
    }

    public function scopeYear(Builder $query, int $year): Builder
    {
        return $query->where('calendar_year', $year);
    }

    public function scopeMonth(Builder $query, int $month): Builder
    {
        return $query->where('calendar_month', $month);
    }

    public function scopeWorkingDays(Builder $query): Builder
    {
        return $query->where('is_working_day', 'Y');
    }

    public function scopeHolidays(Builder $query): Builder
    {
        return $query->where('is_working_day', 'N')->whereNotIn('day_of_week', [0, 6]);
    }

    public function scopeWeekends(Builder $query): Builder
    {
        return $query->whereIn('day_of_week', [0, 6]);
    }
}
