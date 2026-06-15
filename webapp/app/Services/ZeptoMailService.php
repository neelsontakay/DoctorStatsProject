<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZeptoMailService
{
    public function isConfigured(): bool
    {
        $apiKey = config('services.zeptomail.api_key');

        return is_string($apiKey) && $apiKey !== '';
    }

    public function sendHtml(string $toEmail, string $toName, string $subject, string $htmlBody): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('ZeptoMail is not configured.');
        }

        $response = $this->client()->post('/email', [
            'from' => [
                'address' => config('mail.from.address'),
                'name' => config('mail.from.name'),
            ],
            'to' => [
                [
                    'email_address' => [
                        'address' => $toEmail,
                        'name' => $toName,
                    ],
                ],
            ],
            'subject' => $subject,
            'htmlbody' => $htmlBody,
            'bounce_address' => config('services.zeptomail.bounce_address'),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('ZeptoMail delivery failed: '.$response->body());
        }
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.zeptomail.api_url'), '/'))
            ->withHeaders([
                'Authorization' => 'Zoho-enczapikey '.config('services.zeptomail.api_key'),
            ])
            ->acceptJson()
            ->timeout(30);
    }
}
