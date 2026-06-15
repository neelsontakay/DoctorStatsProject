<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\DataFile;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use App\Services\ZohoPay\ZohoPayClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly ZohoPayClient $zohoPayClient,
        private readonly PricingService $pricingService,
        private readonly AuditLogService $auditLogService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * @return array{
     *     payment_id: int,
     *     amount: float,
     *     currency: string,
     *     status: string,
     *     checkout_url: string|null,
     *     breakdown: array<string, float|int>,
     * }
     */
    public function createCheckout(User $user, ?DataFile $dataFile = null, ?Organization $organization = null): array
    {
        $quote = $this->pricingService->quoteForAnalysis($dataFile);

        return DB::transaction(function () use ($user, $organization, $quote, $dataFile): array {
            $payment = Payment::query()->create([
                'user_id' => $user->id,
                'organization_id' => $organization?->id,
                'zoho_payment_id' => 'pending-'.Str::uuid(),
                'amount' => $quote['amount'],
                'currency' => $quote['currency'],
                'status' => PaymentStatus::Pending,
                'payment_type' => PaymentType::PayPerJob,
            ]);

            $checkoutUrl = null;

            if ($this->zohoPayClient->isConfigured()) {
                $session = $this->zohoPayClient->createCheckoutSession([
                    'amount' => $quote['amount'],
                    'currency' => $quote['currency'],
                    'description' => 'DoctorStats analysis job',
                    'reference_id' => (string) $payment->id,
                    'customer' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'redirect_url' => rtrim((string) config('app.url'), '/').'/payments/return',
                    'webhook_url' => rtrim((string) config('app.url'), '/').'/internal/v1/webhooks/zoho',
                    'metadata' => [
                        'payment_record_id' => $payment->id,
                        'data_file_id' => $dataFile?->id,
                    ],
                ]);

                $payment->update([
                    'zoho_payment_id' => $session['payment_id'],
                ]);

                $checkoutUrl = $session['checkout_url'];
            } elseif (config('doctorstats.payment_stub_enabled')) {
                $payment->update([
                    'zoho_payment_id' => 'stub-'.Str::uuid(),
                ]);

                $checkoutUrl = url("/api/v1/payments/{$payment->id}/stub-confirm");
            } else {
                throw new RuntimeException('Zoho Pay is not configured and payment stub mode is disabled.');
            }

            $this->auditLogService->record(
                'payment.checkout_created',
                $user,
                $organization,
                $payment,
                ['amount' => $quote['amount']],
            );

            return [
                'payment_id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status->value,
                'checkout_url' => $checkoutUrl,
                'breakdown' => $quote['breakdown'],
            ];
        });
    }

    public function confirmStubPayment(Payment $payment, User $user): Payment
    {
        if (! config('doctorstats.payment_stub_enabled')) {
            abort(404);
        }

        if ($payment->user_id !== $user->id) {
            abort(403);
        }

        if ($payment->isSucceeded()) {
            return $payment;
        }

        $payment->update([
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
            'invoice_number' => 'STUB-'.$payment->id,
        ]);

        $this->auditLogService->record('payment.stub_confirmed', $user, entity: $payment);

        $payment = $payment->fresh();
        $this->subscriptionService->fulfillPayment($payment);

        return $payment;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): void
    {
        $zohoPaymentId = (string) ($payload['payment_id'] ?? $payload['id'] ?? '');
        $status = strtolower((string) ($payload['status'] ?? ''));
        $referenceId = $payload['reference_id'] ?? $payload['metadata']['payment_record_id'] ?? null;

        $payment = null;

        if ($zohoPaymentId !== '') {
            $payment = Payment::query()->where('zoho_payment_id', $zohoPaymentId)->first();
        }

        if ($payment === null && $referenceId !== null) {
            $payment = Payment::query()->find($referenceId);
        }

        if ($payment === null) {
            throw ValidationException::withMessages([
                'payment' => ['Payment record not found for webhook event.'],
            ]);
        }

        if ($payment->isSucceeded()) {
            return;
        }

        if (in_array($status, ['succeeded', 'success', 'paid', 'completed'], true)) {
            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'paid_at' => now(),
                'invoice_number' => $payload['invoice_number'] ?? $payment->invoice_number,
            ]);

            $this->auditLogService->record('payment.succeeded', $payment->user, $payment->organization, $payment);
            $this->subscriptionService->fulfillPayment($payment->fresh());

            return;
        }

        if (in_array($status, ['failed', 'cancelled', 'canceled'], true)) {
            $payment->update([
                'status' => PaymentStatus::Failed,
            ]);

            $this->auditLogService->record('payment.failed', $payment->user, $payment->organization, $payment);
        }
    }
}
