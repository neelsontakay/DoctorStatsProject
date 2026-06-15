<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_job_id')->constrained()->cascadeOnDelete();
            $table->string('column_name');
            $table->unsignedInteger('column_index');
            $table->enum('data_type', ['categorical', 'numerical', 'date', 'text']);
            $table->string('description')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->enum('variable_type', ['independent', 'dependent', 'control', 'identifier', 'excluded']);
            $table->json('quality_warnings')->nullable();
            $table->timestamps();

            $table->unique(['analysis_job_id', 'column_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_columns');
    }
};
