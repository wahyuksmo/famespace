<?php

use App\Modules\Calendar\Services\WkcalService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('m_wkcal', function (Blueprint $table) {
            $table->uuid('wkcal_id')->primary();
            $table->date('calendar_date')->unique();
            $table->integer('calendar_year')->index();
            $table->integer('calendar_month')->index();
            $table->smallInteger('day_of_week');
            $table->string('day_name', 20);
            $table->char('is_working_day', 1)->default('Y');
            $table->string('event_name', 150);
            $table->string('event_type', 50)->default('bg-success-subtle');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
        });

        // Generate default year 2026 directly into m_wkcal
        (new WkcalService)->generateYear(2026, 'System Migration');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_wkcal');
    }
};
