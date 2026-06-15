<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('Individual plans')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->comment('Organisational plans')->constrained()->cascadeOnDelete();
            $table->enum('plan_tier', [
                'basic',
                'professional',
                'enterprise',
                'org_basic',
                'org_professional',
                'org_enterprise',
            ]);
            $table->enum('billing_cycle', ['monthly', 'annual']);
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])->default('active')->index();
            $table->unsignedInteger('analyses_used_this_period')->default(0);
            $table->unsignedInteger('member_limit')->nullable()->comment('Org plans only');
            $table->boolean('auto_renew')->default(true);
            $table->string('zoho_subscription_id')->nullable()->unique();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
