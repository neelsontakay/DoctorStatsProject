<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_job_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['analysis_job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_job_members');
    }
};
