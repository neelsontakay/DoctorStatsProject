<?php

namespace Tests\Feature\Api;

use App\Enums\AccountType;
use App\Enums\PaymentStatus;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('doctorstats.payment_stub_enabled', true);
    }

    public function test_individual_user_can_quote_subscribe_and_activate_subscription(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::Individual,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/subscriptions/quote?plan_tier=basic&billing_cycle=monthly')
            ->assertOk()
            ->assertJsonPath('data.amount', 2999)
            ->assertJsonPath('data.plan_tier', 'basic');

        $checkout = $this->actingAs($user)
            ->postJson('/api/v1/subscriptions/checkout', [
                'plan_tier' => 'basic',
                'billing_cycle' => 'monthly',
            ])
            ->assertCreated()
            ->assertJsonPath('data.plan_tier', 'basic');

        $paymentId = $checkout->json('data.payment_id');

        $this->actingAs($user)
            ->postJson("/api/v1/payments/{$paymentId}/stub-confirm")
            ->assertOk();

        $this->actingAs($user)
            ->getJson('/api/v1/subscriptions/current')
            ->assertOk()
            ->assertJsonPath('data.plan_tier', 'basic')
            ->assertJsonPath('data.status', 'active');
    }

    public function test_user_can_renew_and_cancel_subscription(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::Individual,
        ]);

        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'plan_tier' => 'professional',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'auto_renew' => true,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDay(),
        ]);

        $renewal = $this->actingAs($user)
            ->postJson('/api/v1/subscriptions/renew')
            ->assertCreated()
            ->assertJsonPath('data.subscription_id', $subscription->id);

        $paymentId = $renewal->json('data.payment_id');

        $this->actingAs($user)
            ->postJson("/api/v1/payments/{$paymentId}/stub-confirm")
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Succeeded->value);

        $subscription->refresh();
        $this->assertSame(0, $subscription->analyses_used_this_period);
        $this->assertTrue($subscription->ends_at->greaterThan(now()->addDay()));

        $cancelResponse = $this->actingAs($user)
            ->postJson('/api/v1/subscriptions/cancel')
            ->assertOk();

        $this->assertSame(
            'cancelled',
            $cancelResponse->json('status') ?? $cancelResponse->json('data.status'),
        );
        $this->assertFalse(
            $cancelResponse->json('auto_renew') ?? $cancelResponse->json('data.auto_renew'),
        );
    }

    public function test_org_admin_can_purchase_organization_subscription(): void
    {
        $admin = User::factory()->create([
            'account_type' => AccountType::Organizational,
        ]);

        $organization = Organization::factory()->create([
            'admin_user_id' => $admin->id,
        ]);

        OrganizationMember::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $admin->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $checkout = $this->actingAs($admin)
            ->postJson('/api/v1/subscriptions/checkout', [
                'plan_tier' => 'org_basic',
                'billing_cycle' => 'annual',
                'organization_id' => $organization->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.plan_tier', 'org_basic');

        $paymentId = $checkout->json('data.payment_id');

        $this->actingAs($admin)
            ->postJson("/api/v1/payments/{$paymentId}/stub-confirm")
            ->assertOk();

        $this->actingAs($admin)
            ->getJson("/api/v1/subscriptions/current?organization_id={$organization->id}")
            ->assertOk()
            ->assertJsonPath('data.plan_tier', 'org_basic')
            ->assertJsonPath('data.organization_id', $organization->id);
    }
}
