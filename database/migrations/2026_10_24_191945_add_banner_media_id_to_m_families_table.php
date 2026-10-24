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
            $table->foreignId('banner_media_id')
                ->nullable()
                ->after('currency')
                ->constrained('t_album_media')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_families', function (Blueprint $table) {
            $table->dropForeign(['banner_media_id']);
            $table->dropColumn('banner_media_id');
        });
    }
};
