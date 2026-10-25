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
        Schema::table('t_subscription_payments', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('reference_code');
            $table->string('transfer_time')->nullable()->after('proof_image');
            $table->text('admin_notes')->nullable()->after('transfer_time');
            $table->timestamp('verified_at')->nullable()->after('admin_notes');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('m_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('t_subscription_payments', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'proof_image',
                'transfer_time',
                'admin_notes',
                'verified_at',
                'verified_by',
            ]);
        });
    }
};
