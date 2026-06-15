<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('analysis_job_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('zoho_payment_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])->default('pending')->index();
            $table->enum('payment_type', ['pay_per_job', 'subscription', 'renewal']);
            $table->string('invoice_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
