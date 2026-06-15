<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateCheckoutRequest;
use App\Http\Resources\PaymentResource;
use App\Models\DataFile;
use App\Models\Organization;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly PricingService $pricingService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return PaymentResource::collection($payments);
    }

    public function quote(Request $request): JsonResponse
    {
        $dataFile = null;

        if ($request->filled('data_file_id')) {
            $dataFile = DataFile::query()->findOrFail($request->integer('data_file_id'));
            $this->authorize('view', $dataFile);
        }

        return response()->json([
            'data' => $this->pricingService->quoteForAnalysis($dataFile),
        ]);
    }

    public function checkout(CreateCheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $dataFile = null;
        $organization = null;

        if ($request->filled('data_file_id')) {
            $dataFile = DataFile::query()->findOrFail($request->integer('data_file_id'));
            $this->authorize('view', $dataFile);
        }

        if ($request->filled('organization_id')) {
            $organization = Organization::query()->findOrFail($request->integer('organization_id'));
            $this->authorize('view', $organization);
        }

        $checkout = $this->paymentService->createCheckout($user, $dataFile, $organization);

        return response()->json(['data' => $checkout], 201);
    }

    public function stubConfirm(Request $request, Payment $payment): PaymentResource
    {
        $payment = $this->paymentService->confirmStubPayment($payment, $request->user());

        return new PaymentResource($payment);
    }
}
