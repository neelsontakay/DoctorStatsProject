<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('token', 64)->unique();
            $table->enum('role', ['admin', 'analyst', 'viewer'])->default('viewer');
            $table->timestamp('expires_at');
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_invitations');
    }
};
