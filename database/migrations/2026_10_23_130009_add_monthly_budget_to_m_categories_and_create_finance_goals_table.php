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
        Schema::table('m_categories', function (Blueprint $table) {
            $table->decimal('monthly_budget', 15, 2)->default(0.00)->after('color');
        });

        Schema::create('t_finance_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->string('title', 150);
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0.00);
            $table->string('icon', 50)->default('🎯');
            $table->string('color', 20)->default('#2F5233');
            $table->date('target_date')->nullable();
            $table->string('note', 255)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['family_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_finance_goals');

        Schema::table('m_categories', function (Blueprint $table) {
            $table->dropColumn('monthly_budget');
        });
    }
};
