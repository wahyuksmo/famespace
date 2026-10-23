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
        Schema::create('t_category_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('m_categories')->cascadeOnDelete();
            $table->string('period', 7); // Format: YYYY-MM
            $table->decimal('budget_amount', 15, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['family_id', 'category_id', 'period']);
            $table->index(['family_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_category_budgets');
    }
};
