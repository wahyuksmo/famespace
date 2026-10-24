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
        Schema::create('t_pantry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('category', 50)->default('dapur'); // dapur, kulkas, obat, kebersihan, lainnya
            $table->decimal('quantity', 8, 2)->default(1);
            $table->string('unit', 30)->default('pcs'); // pcs, kg, liter, botol, kotak, sachet, pack
            $table->decimal('min_quantity', 8, 2)->default(1);
            $table->date('expired_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('m_users')->nullOnDelete();
            $table->timestamps();

            $table->index(['family_id', 'category']);
            $table->index(['family_id', 'quantity', 'min_quantity']);
            $table->index(['family_id', 'expired_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_pantry_items');
    }
};
