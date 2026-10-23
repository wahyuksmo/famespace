<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Plan::firstOrCreate(
            ['code' => 'lifetime'],
            [
                'name' => 'Famespace Pro Lifetime',
                'price' => 150000.00,
                'duration_days' => 36500,
                'features' => ['overview', 'chores', 'finance', 'members', 'timeline', 'calendar', 'unlimited_members'],
                'is_active' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Plan::where('code', 'lifetime')->delete();
    }
};
