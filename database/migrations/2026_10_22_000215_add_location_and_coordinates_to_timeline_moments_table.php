<?php

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
        Schema::table('t_timeline_moments', function (Blueprint $table) {
            $table->string('location_name', 150)->nullable()->after('story');
            $table->decimal('latitude', 10, 8)->nullable()->after('location_name');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_timeline_moments', function (Blueprint $table) {
            $table->dropColumn(['location_name', 'latitude', 'longitude']);
        });
    }
};
