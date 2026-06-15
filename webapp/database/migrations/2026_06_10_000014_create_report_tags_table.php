<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('Personal tags')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->comment('Org-defined tags')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('report_report_tag', function (Blueprint $table) {
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['report_id', 'report_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_report_tag');
        Schema::dropIfExists('report_tags');
    }
};
