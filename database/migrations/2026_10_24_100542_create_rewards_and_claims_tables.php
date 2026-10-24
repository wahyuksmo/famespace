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
        Schema::create('m_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->integer('points')->default(10);
            $table->string('icon', 50)->default('🎁');
            $table->integer('stock')->nullable(); // null = unlimited
            $table->foreignId('created_by_user_id')->nullable()->constrained('m_users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['family_id', 'is_active']);
        });

        Schema::create('t_reward_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained('m_rewards')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('m_users')->cascadeOnDelete();
            $table->integer('points_spent');
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->text('notes')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('m_users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_reward_claims');
        Schema::dropIfExists('m_rewards');
    }
};
