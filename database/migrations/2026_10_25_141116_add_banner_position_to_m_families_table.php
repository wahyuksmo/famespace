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
        Schema::table('m_families', function (Blueprint $table) {
            $table->string('banner_position', 50)->nullable()->default('50% 50%')->after('banner_media_id');
            $table->string('banner_fit', 20)->nullable()->default('cover')->after('banner_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_families', function (Blueprint $table) {
            $table->dropColumn(['banner_position', 'banner_fit']);
        });
    }
};
