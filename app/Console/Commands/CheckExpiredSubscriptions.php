<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscription:check-expired';

    protected $description = 'Periksa dan tandai langganan keluarga yang sudah melewati tanggal kedaluwarsa';

    public function handle(): int
    {
        $count = Subscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
                'updated_at' => now(),
            ]);

        $this->info("Pemeriksaan selesai. {$count} langganan ditandai sebagai expired.");

        return Command::SUCCESS;
    }
}
