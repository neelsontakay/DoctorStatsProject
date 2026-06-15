<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_job_id')->unique()->comment('1:1 with job')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('report_folder_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('executive_summary')->nullable();
            $table->text('ai_interpretation')->nullable();
            $table->string('web_html_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('excel_path')->nullable();
            $table->boolean('is_favourite')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
