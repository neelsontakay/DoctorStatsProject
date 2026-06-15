<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ZohoPay\ZohoPayClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SubscriptionService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ZohoPayClient $zohoPayClient,
    ) {}

    public function activeFor(User $user, ?Organization $organization = null): ?Subscription
    {
        if ($organization !== null) {
            return $organization->subscriptions()->active()->first();
        }

        if ($user->account_type === AccountType::Organizational) {
            return null;
        }

        return $user->subscriptions()->active()->first();
    }

    public function assertCanRunAnalysis(User $user, ?Organization $organization, ?Subscription $subscription): Subscription
    {
        if ($subscription === null || ! $this->isUsable($subscription)) {
            throw ValidationException::withMessages([
                'payment_method' => ['An active subscription is required to run this analysis.'],
            ]);
        }

        if ($organization !== null) {
            if ($subscription->organization_id !== $organization->id) {
                throw ValidationException::withMessages([
                    'payment_method' => ['Organizational analyses must use the organization subscription.'],
                ]);
            }
        } elseif ($subscription->organization_id !== null) {
            throw ValidationException::withMessages([
                'payment_method' => ['Personal analyses cannot use an organizational subscription.'],
            ]);
        }

        $limit = config('doctorstats.subscription_analysis_limits')[$subscription->plan_tier] ?? null;

        if ($limit !== null && $subscription->analyses_used_this_period >= $limit) {
            throw ValidationException::withMessages([
                'payment_method' => ['Your subscription analysis limit for this billing period has been reached.'],
            ]);
        }

        return $subscription;
    }

    public function incrementUsage(Subscription $subscription): void
    {
        $subscription->increment('analyses_used_this_period');
    }

    /**
     * @return array{amount: float, currency: string, plan_tier: string, billing_cycle: string, breakdown: array<string, float|string>}
     */
    public function quote(string $planTier, string $billingCycle): array
    {
        $plan = $this->planConfig($planTier);
        $amount = $this->amountFor($planTier, $billingCycle);

        return [
            'amount' => $amount,
            'currency' => (string) config('doctorstats.pricing.currency', 'INR'),
            'plan_tier' => $planTier,
            'billing_cycle' => $billingCycle,
            'breakdown' => [
                'plan_name' => $plan['name'],
                'billing_cycle' => $billingCycle,
                'analysis_limit' => $plan['analysis_limit'],
            ],
        ];
    }

    /**
     * @return array{
     *     payment_id: int,
     *     amount: float,
     *     currency: string,
     *     status: string,
     *     checkout_url: string|null,
     *     plan_tier: string,
     *     billing_cycle: string,
     * }
     */
    public function createCheckout(User $user, string $planTier, string $billingCycle, ?Organization $organization = null): array
    {
        $this->assertPlanAllowed($user, $planTier, $organization);

        $quote = $this->quote($planTier, $billingCycle);

        return DB::transaction(function () use ($user, $organization, $planTier, $billingCycle, $quote): array {
            $payment = Payment::query()->create([
                'user_id' => $user->id,
                'organization_id' => $organization?->id,
                'zoho_payment_id' => 'pending-'.Str::uuid(),
                'amount' => $quote['amount'],
                'currency' => $quote['currency'],
                'status' => PaymentStatus::Pending,
                'payment_type' => PaymentType::Subscription,
            ]);

            $this->rememberCheckoutIntent($payment->id, [
                'action' => 'subscribe',
                'plan_tier' => $planTier,
                'billing_cycle' => $billingCycle,
                'organization_id' => $organization?->id,
            ]);

            $checkoutUrl = $this->resolveCheckoutUrl($payment, $user, $quote, 'DoctorStats subscription');

            $this->auditLogService->record(
                'subscription.checkout_created',
                $user,
                $organization,
                $payment,
                ['plan_tier' => $planTier, 'billing_cycle' => $billingCycle],
            );

            return [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status->value,
                'checkout_url' => $checkoutUrl,
                'plan_tier' => $planTier,
                'billing_cycle' => $billingCycle,
            ];
        });
    }

    /**
     * @return array{
     *     payment_id: int,
     *     amount: float,
     *     currency: string,
     *     status: string,
     *     checkout_url: string|null,
     *     subscription_id: int,
     * }
     */
    public function createRenewalCheckout(User $user, ?Organization $organization = null): array
    {
        $subscription = $this->requireActiveSubscription($user, $organization);

        return DB::transaction(function () use ($user, $organization, $subscription): array {
            $amount = $this->amountFor($subscription->plan_tier, $subscription->billing_cycle);

            $payment = Payment::query()->create([
                'user_id' => $user->id,
                'organization_id' => $organization?->id,
                'subscription_id' => $subscription->id,
                'zoho_payment_id' => 'pending-'.Str::uuid(),
                'amount' => $amount,
                'currency' => (string) config('doctorstats.pricing.currency', 'INR'),
                'status' => PaymentStatus::Pending,
                'payment_type' => PaymentType::Renewal,
            ]);

            $this->rememberCheckoutIntent($payment->id, [
                'action' => 'renew',
                'subscription_id' => $subscription->id,
            ]);

            $quote = [
                'amount' => $amount,
                'currency' => $payment->currency,
            ];

            $checkoutUrl = $this->resolveCheckoutUrl($payment, $user, $quote, 'DoctorStats subscription renewal');

            $this->auditLogService->record(
                'subscription.renewal_checkout_created',
                $user,
                $organization,
                $payment,
                ['subscription_id' => $subscription->id],
            );

            return [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status->value,
                'checkout_url' => $checkoutUrl,
                'subscription_id' => $subscription->id,
            ];
        });
    }

    public function cancel(User $user, ?Organization $organization = null): Subscription
    {
        $subscription = $this->requireActiveSubscription($user, $organization);

        if ($organization !== null && ! $user->isAdminOf($organization)) {
            abort(403);
        }

        $subscription->update([
            'auto_renew' => false,
            'status' => 'cancelled',
        ]);

        $this->auditLogService->record(
            'subscription.cancelled',
            $user,
            $organization,
            $subscription,
            ['ends_at' => $subscription->ends_at?->toIso8601String()],
        );

        return $subscription->fresh();
    }

    public function fulfillPayment(Payment $payment): ?Subscription
    {
        if (! $payment->isSucceeded()) {
            return null;
        }

        return match ($payment->payment_type) {
            PaymentType::Subscription => $this->activateFromCheckout($payment),
            PaymentType::Renewal => $this->renewFromPayment($payment),
            default => null,
        };
    }

    private function activateFromCheckout(Payment $payment): Subscription
    {
        $intent = $this->checkoutIntent($payment->id);

        if ($intent === null || $intent['action'] !== 'subscribe') {
            throw new RuntimeException('Subscription checkout intent not found.');
        }

        $planTier = (string) $intent['plan_tier'];
        $billingCycle = (string) $intent['billing_cycle'];
        $organizationId = $intent['organization_id'] ?? null;

        $startsAt = now();
        $endsAt = $this->periodEnd($startsAt, $billingCycle);

        $subscription = Subscription::query()->updateOrCreate(
            [
                'user_id' => $organizationId === null ? $payment->user_id : null,
                'organization_id' => $organizationId,
            ],
            [
                'plan_tier' => $planTier,
                'billing_cycle' => $billingCycle,
                'status' => 'active',
                'analyses_used_this_period' => 0,
                'member_limit' => $this->planConfig($planTier)['member_limit'] ?? null,
                'auto_renew' => true,
                'zoho_subscription_id' => 'stub-'.Str::uuid(),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ],
        );

        $payment->update(['subscription_id' => $subscription->id]);
        Cache::forget($this->checkoutCacheKey($payment->id));

        $this->auditLogService->record(
            'subscription.activated',
            $payment->user,
            $subscription->organization,
            $subscription,
            ['payment_id' => $payment->id],
        );

        return $subscription->fresh();
    }

    private function renewFromPayment(Payment $payment): Subscription
    {
        $subscription = $payment->subscription_id !== null
            ? Subscription::query()->findOrFail($payment->subscription_id)
            : null;

        if ($subscription === null) {
            $intent = $this->checkoutIntent($payment->id);
            $subscriptionId = $intent['subscription_id'] ?? null;
            $subscription = $subscriptionId !== null
                ? Subscription::query()->findOrFail($subscriptionId)
                : null;
        }

        if ($subscription === null) {
            throw new RuntimeException('Subscription record not found for renewal payment.');
        }

        $currentEnd = $subscription->ends_at ?? now();
        $anchor = $currentEnd->isFuture() ? $currentEnd : now();
        $newEnd = $this->periodEnd($anchor, $subscription->billing_cycle);

        $subscription->update([
            'status' => 'active',
            'auto_renew' => true,
            'analyses_used_this_period' => 0,
            'ends_at' => $newEnd,
        ]);

        $payment->update(['subscription_id' => $subscription->id]);
        Cache::forget($this->checkoutCacheKey($payment->id));

        $this->auditLogService->record(
            'subscription.renewed',
            $payment->user,
            $subscription->organization,
            $subscription,
            ['payment_id' => $payment->id],
        );

        return $subscription->fresh();
    }

    private function requireActiveSubscription(User $user, ?Organization $organization): Subscription
    {
        $subscription = $this->activeFor($user, $organization);

        if ($subscription === null || ! $this->isUsable($subscription)) {
            throw ValidationException::withMessages([
                'subscription' => ['No active subscription was found to renew or cancel.'],
            ]);
        }

        return $subscription;
    }

    private function isUsable(Subscription $subscription): bool
    {
        if ($subscription->ends_at !== null && $subscription->ends_at->isPast()) {
            return false;
        }

        return in_array($subscription->status, ['active', 'cancelled'], true);
    }

    private function assertPlanAllowed(User $user, string $planTier, ?Organization $organization): void
    {
        $plan = $this->planConfig($planTier);
        $allowedAccountTypes = $plan['account_types'] ?? [];

        if ($organization !== null) {
            if (! in_array('organizational', $allowedAccountTypes, true)) {
                throw ValidationException::withMessages([
                    'plan_tier' => ['This plan is not available for organizational accounts.'],
                ]);
            }

            if (! $user->isAdminOf($organization)) {
                abort(403);
            }

            return;
        }

        if ($user->account_type === AccountType::Organizational) {
            throw ValidationException::withMessages([
                'organization_id' => ['Organizational subscriptions must be purchased for an organization.'],
            ]);
        }

        if (! in_array('individual', $allowedAccountTypes, true)) {
            throw ValidationException::withMessages([
                'plan_tier' => ['This plan is not available for individual accounts.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function planConfig(string $planTier): array
    {
        $plan = config("doctorstats.subscription_plans.{$planTier}");

        if (! is_array($plan)) {
            throw ValidationException::withMessages([
                'plan_tier' => ['The selected subscription plan is invalid.'],
            ]);
        }

        return $plan;
    }

    private function amountFor(string $planTier, string $billingCycle): float
    {
        $plan = $this->planConfig($planTier);

        return (float) ($billingCycle === 'annual'
            ? $plan['annual_amount']
            : $plan['monthly_amount']);
    }

    private function periodEnd(Carbon $startsAt, string $billingCycle): Carbon
    {
        return $billingCycle === 'annual'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();
    }

    /**
     * @param  array<string, mixed>  $intent
     */
    private function rememberCheckoutIntent(int $paymentId, array $intent): void
    {
        Cache::put($this->checkoutCacheKey($paymentId), $intent, now()->addDay());
    }

    /**
     * @return array<string, mixed>|null
     */
    private function checkoutIntent(int $paymentId): ?array
    {
        $intent = Cache::get($this->checkoutCacheKey($paymentId));

        return is_array($intent) ? $intent : null;
    }

    private function checkoutCacheKey(int $paymentId): string
    {
        return "subscription_checkout:{$paymentId}";
    }

    /**
     * @param  array{amount: float, currency: string}  $quote
     */
    private function resolveCheckoutUrl(Payment $payment, User $user, array $quote, string $description): ?string
    {
        if ($this->zohoPayClient->isConfigured()) {
            $session = $this->zohoPayClient->createCheckoutSession([
                'amount' => $quote['amount'],
                'currency' => $quote['currency'],
                'description' => $description,
                'reference_id' => (string) $payment->id,
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'redirect_url' => rtrim((string) config('app.url'), '/').'/subscriptions/return',
                'webhook_url' => rtrim((string) config('app.url'), '/').'/internal/v1/webhooks/zoho',
                'metadata' => [
                    'payment_record_id' => $payment->id,
                    'payment_type' => $payment->payment_type->value,
                ],
            ]);

            $payment->update([
                'zoho_payment_id' => $session['payment_id'],
            ]);

            return $session['checkout_url'];
        }

        if (config('doctorstats.payment_stub_enabled')) {
            $payment->update([
                'zoho_payment_id' => 'stub-'.Str::uuid(),
            ]);

            return url("/api/v1/payments/{$payment->id}/stub-confirm");
        }

        throw new RuntimeException('Zoho Pay is not configured and payment stub mode is disabled.');
    }
}
