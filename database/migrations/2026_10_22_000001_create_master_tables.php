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
        Schema::create('m_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->decimal('price', 15, 2)->default(0.00);
            $table->integer('duration_days')->default(30);
            $table->jsonb('features')->default('[]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('m_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->nullable()->constrained('m_families')->nullOnDelete();
            $table->string('name', 50);
            $table->string('type', 10); // income | expense
            $table->string('icon', 50)->default('receipt');
            $table->string('color', 20)->default('#2F5233');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_categories');
        Schema::dropIfExists('m_plans');
    }
};
