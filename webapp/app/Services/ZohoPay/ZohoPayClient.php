<?php

namespace App\Services\ZohoPay;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZohoPayClient
{
    public function isConfigured(): bool
    {
        $apiKey = config('services.zoho_pay.api_key');
        $accountId = config('services.zoho_pay.account_id');

        return is_string($apiKey) && $apiKey !== ''
            && is_string($accountId) && $accountId !== '';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{payment_id: string, checkout_url: string}
     */
    public function createCheckoutSession(array $payload): array
    {
        $response = $this->client()->post(
            '/accounts/'.config('services.zoho_pay.account_id').'/payments',
            $payload,
        );

        if (! $response->successful()) {
            throw new RuntimeException('Zoho Pay checkout failed: '.$response->body());
        }

        $body = $response->json();

        return [
            'payment_id' => (string) ($body['payment_id'] ?? $body['id'] ?? ''),
            'checkout_url' => (string) ($body['checkout_url'] ?? $body['payment_url'] ?? ''),
        ];
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        $secret = config('services.zoho_pay.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            return app()->environment(['local', 'testing']);
        }

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.zoho_pay.base_url'), '/'))
            ->withToken((string) config('services.zoho_pay.api_key'))
            ->acceptJson()
            ->timeout(30);
    }
}
