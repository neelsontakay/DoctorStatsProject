<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\ZohoPay\ZohoPayClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZohoWebhookController extends Controller
{
    public function __construct(
        private readonly ZohoPayClient $zohoPayClient,
        private readonly PaymentService $paymentService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Zoho-Signature')
            ?? $request->header('X-Zoho-Webhook-Signature');

        if (! $this->zohoPayClient->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        /** @var array<string, mixed> $data */
        $data = $request->json()->all();

        $this->paymentService->handleWebhook($data);

        return response()->json(['received' => true]);
    }
}
