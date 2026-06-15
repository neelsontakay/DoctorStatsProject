<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\AnalysisJob;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentGateService
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * @return array{payment: Payment|null, subscription: \App\Models\Subscription|null}
     */
    public function resolve(
        User $user,
        ?Organization $organization,
        PaymentMethod $paymentMethod,
        ?int $paymentId = null,
        ?int $subscriptionId = null,
    ): array {
        return match ($paymentMethod) {
            PaymentMethod::Subscription => $this->resolveSubscription($user, $organization, $subscriptionId),
            PaymentMethod::PayPerJob => [
                'payment' => $this->resolvePayPerJob($user, $organization, $paymentId),
                'subscription' => null,
            ],
        };
    }

    public function attachPaymentToJob(AnalysisJob $job, Payment $payment): void
    {
        $payment->update(['analysis_job_id' => $job->id]);
        $job->update(['payment_id' => $payment->id]);
    }

    private function resolveSubscription(User $user, ?Organization $organization, ?int $subscriptionId): array
    {
        $subscription = $subscriptionId !== null
            ? \App\Models\Subscription::query()->find($subscriptionId)
            : $this->subscriptionService->activeFor($user, $organization);

        $subscription = $this->subscriptionService->assertCanRunAnalysis($user, $organization, $subscription);

        return [
            'payment' => null,
            'subscription' => $subscription,
        ];
    }

    private function resolvePayPerJob(User $user, ?Organization $organization, ?int $paymentId): Payment
    {
        if ($paymentId !== null) {
            $payment = Payment::query()->find($paymentId);

            if ($payment === null || ! $payment->isSucceeded()) {
                throw ValidationException::withMessages([
                    'payment_id' => ['A successful payment is required before the analysis can start.'],
                ]);
            }

            if ($payment->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'payment_id' => ['The selected payment does not belong to this user.'],
                ]);
            }

            if ($payment->analysis_job_id !== null) {
                throw ValidationException::withMessages([
                    'payment_id' => ['This payment has already been used for an analysis job.'],
                ]);
            }

            if ($organization !== null && $payment->organization_id !== $organization->id) {
                throw ValidationException::withMessages([
                    'payment_id' => ['Organizational analyses must use an organization payment.'],
                ]);
            }

            return $payment;
        }

        if (! config('doctorstats.payment_stub_enabled')) {
            throw ValidationException::withMessages([
                'payment_id' => ['Payment is required before the analysis can start.'],
            ]);
        }

        return Payment::query()->create([
            'user_id' => $user->id,
            'organization_id' => $organization?->id,
            'zoho_payment_id' => 'stub-'.Str::uuid(),
            'amount' => config('doctorstats.pay_per_job_amount'),
            'currency' => 'INR',
            'status' => PaymentStatus::Succeeded,
            'payment_type' => PaymentType::PayPerJob,
            'paid_at' => now(),
        ]);
    }
}
