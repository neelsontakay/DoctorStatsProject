<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_files', function (Blueprint $table) {
            $table->json('preview_stats')->nullable()->after('virus_scan_status');
            $table->timestamp('preview_stats_computed_at')->nullable()->after('preview_stats');
        });
    }

    public function down(): void
    {
        Schema::table('data_files', function (Blueprint $table) {
            $table->dropColumn(['preview_stats', 'preview_stats_computed_at']);
        });
    }
};
