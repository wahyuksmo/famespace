<?php

use App\Models\Category;
use App\Models\Family;
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
            $table->string('type', 20)->change();
        });

        Schema::table('t_timeline_moments', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained('m_categories')->nullOnDelete();
        });

        // Seed default categories for all existing families
        $families = Family::all();
        foreach ($families as $family) {
            Category::seedDefaultsForFamily($family->id);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_timeline_moments', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('m_categories', function (Blueprint $table) {
            $table->string('type', 10)->change();
        });
    }
};
