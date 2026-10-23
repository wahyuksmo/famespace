<?php

namespace App\Modules\Calendar\Services;

use App\Models\Wkcal;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WkcalService
{
    public const DAY_NAMES = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    /**
     * Indonesian national holidays mapping.
     * Fixed dates (m-d) + year-specific moveable holidays.
     */
    public function getHolidaysForYear(int $year): array
    {
        // Fixed annual holidays
        $holidays = [
            '01-01' => 'Tahun Baru Masehi',
            '05-01' => 'Hari Buruh Internasional',
            '06-01' => 'Hari Lahir Pancasila',
            '08-17' => 'Hari Kemerdekaan RI',
            '12-25' => 'Hari Raya Natal',
        ];

        // Specific holidays for 2026
        if ($year === 2026) {
            $holidays['01-16'] = "Isra Mi'raj Nabi Muhammad SAW";
            $holidays['02-17'] = 'Tahun Baru Imlek 2577 Kongzili';
            $holidays['03-20'] = 'Hari Suci Nyepi (Tahun Baru Saka 1948)';
            $holidays['03-21'] = 'Hari Raya Idul Fitri 1447 Hijriah';
            $holidays['03-22'] = 'Hari Raya Idul Fitri 1447 Hijriah (Hari Ke-2)';
            $holidays['04-03'] = 'Wafat Yesus Kristus';
            $holidays['04-05'] = 'Hari Paskah';
            $holidays['05-14'] = 'Kenaikan Yesus Kristus';
            $holidays['05-27'] = 'Hari Raya Idul Adha 1447 Hijriah';
            $holidays['05-31'] = 'Hari Raya Waisak 2570 BE';
            $holidays['06-16'] = 'Tahun Baru Islam 1448 Hijriah';
            $holidays['08-25'] = 'Maulid Nabi Muhammad SAW';
        }

        return $holidays;
    }

    /**
     * Generate full year records in m_wkcal.
     */
    public function generateYear(int $year = 2026, string $actor = 'System'): array
    {
        return DB::transaction(function () use ($year, $actor) {
            DB::table('m_wkcal')->where('calendar_year', $year)->delete();

            $holidays = $this->getHolidaysForYear($year);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $period = CarbonPeriod::create($startDate, $endDate);

            $records = [];
            $workingDays = 0;
            $holidayCount = 0;
            $now = now()->toDateTimeString();

            foreach ($period as $date) {
                $dayOfWeek = (int) $date->dayOfWeek;
                $dayName = self::DAY_NAMES[$dayOfWeek] ?? $date->locale('id')->isoFormat('dddd');
                $monthDay = $date->format('m-d');

                $isHoliday = isset($holidays[$monthDay]);
                $isWeekend = in_array($dayOfWeek, [0, 6], true);

                if ($isHoliday) {
                    $isWorkingDay = 'N';
                    $eventName = $holidays[$monthDay];
                    $eventType = 'bg-danger-subtle';
                    $startTime = null;
                    $endTime = null;
                    $desc = 'Hari Libur Nasional';
                    $holidayCount++;
                } elseif ($isWeekend) {
                    $isWorkingDay = 'N';
                    $eventName = 'Akhir Pekan ('.$dayName.')';
                    $eventType = 'bg-danger-subtle';
                    $startTime = null;
                    $endTime = null;
                    $desc = 'Libur Akhir Pekan';
                } else {
                    $isWorkingDay = 'Y';
                    $eventName = 'Hari Kerja';
                    $eventType = 'bg-success-subtle';
                    $startTime = '08:00:00';
                    $endTime = '17:00:00';
                    $desc = 'Hari operasional kerja';
                    $workingDays++;
                }

                $records[] = [
                    'wkcal_id' => (string) Str::uuid(),
                    'calendar_date' => $date->toDateString(),
                    'calendar_year' => $year,
                    'calendar_month' => (int) $date->month,
                    'day_of_week' => $dayOfWeek,
                    'day_name' => $dayName,
                    'is_working_day' => $isWorkingDay,
                    'event_name' => $eventName,
                    'event_type' => $eventType,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => null,
                    'description' => $desc,
                    'created_by' => $actor,
                    'updated_by' => $actor,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Chunk insert for database efficiency
            foreach (array_chunk($records, 100) as $chunk) {
                DB::table('m_wkcal')->insert($chunk);
            }

            return [
                'year' => $year,
                'total_days' => count($records),
                'working_days' => $workingDays,
                'holidays' => $holidayCount,
            ];
        });
    }

    /**
     * Get records for a specific month and year.
     */
    public function getMonthDays(int $year, int $month): Collection
    {
        return Wkcal::query()
            ->where('calendar_year', $year)
            ->where('calendar_month', $month)
            ->orderBy('calendar_date', 'asc')
            ->get();
    }
}
