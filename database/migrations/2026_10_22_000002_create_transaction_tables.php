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
        // 1. Langganan Keluarga Aktif
        Schema::create('t_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('m_plans')->cascadeOnDelete();
            $table->string('status', 20)->default('active'); // active | expired | cancelled
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();

            $table->index(['family_id', 'status']);
        });

        // 2. Invoice & Transaksi Pembayaran Langganan Pro
        Schema::create('t_subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('subscription_id')->nullable()->constrained('t_subscriptions')->nullOnDelete();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('m_users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('m_plans')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('Simulasi');
            $table->string('status', 20)->default('pending'); // pending | paid | expired | failed
            $table->timestamp('paid_at')->nullable();
            $table->string('reference_code', 100)->nullable();
            $table->timestamps();

            $table->index('family_id');
        });

        // 3. Catatan Kas Keluarga
        Schema::create('t_finances', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('m_users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('m_categories')->nullOnDelete();
            $table->string('type', 10); // income | expense
            $table->decimal('amount', 15, 2);
            $table->string('description', 255);
            $table->string('receipt_path', 255)->nullable();
            $table->date('transaction_date');
            $table->timestamps();

            $table->index(['family_id', 'transaction_date']);
        });

        // 4. Tugas Harian & Gamifikasi Poin
        Schema::create('t_chores', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('m_users')->nullOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->integer('points')->default(10);
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending'); // pending | completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'status']);
        });

        // 5. Kalender & Agenda Bersama
        Schema::create('t_events', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('m_users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('location', 150)->nullable();
            $table->timestamps();

            $table->index(['family_id', 'start_time']);
        });

        // 6. Linimasa Kenangan & Catatan Liburan
        Schema::create('t_timeline_moments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('family_id')->constrained('m_families')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('m_users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('story')->nullable();
            $table->string('image_path', 255);
            $table->date('event_date');
            $table->timestamps();

            $table->index(['family_id', 'event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_timeline_moments');
        Schema::dropIfExists('t_events');
        Schema::dropIfExists('t_chores');
        Schema::dropIfExists('t_finances');
        Schema::dropIfExists('t_subscription_payments');
        Schema::dropIfExists('t_subscriptions');
    }
};
