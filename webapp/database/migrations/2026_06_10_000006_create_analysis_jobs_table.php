<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique()->comment('Public unique job ID');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('data_file_id')->constrained()->restrictOnDelete();
            $table->text('objectives');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->index();
            $table->enum('access_scope', ['all_members', 'specific_members', 'private'])->default('private');
            $table->enum('payment_method', ['pay_per_job', 'subscription']);
            // FK constraint added in a follow-up migration because payments
            // also references analysis_jobs (circular dependency).
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_jobs');
    }
};
