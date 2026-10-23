<?php

namespace App\Console\Commands;

use App\Modules\Calendar\Services\WkcalService;
use Illuminate\Console\Command;

class GenerateWkcalCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wkcal:generate {year=2026 : Tahun kalender yang akan digenerate}';

    /**
     * The console command description.
     */
    protected $description = 'Generate kalender kerja master (m_wkcal) untuk tahun tertentu';

    /**
     * Execute the console command.
     */
    public function handle(WkcalService $service): int
    {
        $year = (int) $this->argument('year');

        $this->info("Memulai generate m_wkcal untuk tahun {$year}...");

        $res = $service->generateYear($year, 'Artisan Command');

        $this->info("Berhasil generate {$res['total_days']} hari: {$res['working_days']} hari kerja, {$res['holidays']} hari libur.");

        return Command::SUCCESS;
    }
}
