<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_job_id')->constrained()->cascadeOnDelete();
            $table->string('test_name');
            $table->string('test_category')->index();
            $table->json('parameters')->nullable();
            $table->decimal('test_statistic', 20, 6)->nullable();
            $table->decimal('p_value', 11, 10)->nullable();
            $table->json('confidence_intervals')->nullable();
            $table->json('effect_sizes')->nullable();
            $table->json('assumptions_validation')->nullable();
            $table->json('raw_output')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
