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
        Schema::create('t_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('m_users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('content');
            $table->string('color', 30)->default('yellow'); // yellow, mint, blue, pink, orange, lavender
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['family_id', 'is_pinned', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_notes');
    }
};
