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
        Schema::create('m_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('m_users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'created_at']);
        });

        Schema::create('t_album_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('m_albums')->cascadeOnDelete();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('m_users')->cascadeOnDelete();
            $table->string('media_type', 10); // 'photo' or 'video'
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'media_type']);
            $table->index(['album_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_album_media');
        Schema::dropIfExists('m_albums');
    }
};
