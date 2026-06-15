<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_filename');
            $table->string('s3_path');
            $table->enum('format', ['xlsx', 'xls', 'csv']);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('sheet_name')->nullable()->comment('Excel multi-sheet support');
            $table->enum('virus_scan_status', ['pending', 'clean', 'rejected'])->default('pending')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_files');
    }
};
