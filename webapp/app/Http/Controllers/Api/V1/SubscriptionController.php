<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CancelSubscriptionRequest;
use App\Http\Requests\Api\V1\RenewSubscriptionRequest;
use App\Http\Requests\Api\V1\SubscribeCheckoutRequest;
use App\Http\Requests\Api\V1\SubscriptionQuoteRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Organization;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function plans(): JsonResponse
    {
        return response()->json([
            'data' => config('doctorstats.subscription_plans'),
        ]);
    }

    public function quote(SubscriptionQuoteRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->subscriptionService->quote(
                $request->validated('plan_tier'),
                $request->validated('billing_cycle'),
            ),
        ]);
    }

    public function current(Request $request): JsonResponse
    {
        $organization = $this->resolveOrganization($request);

        $subscription = $this->subscriptionService->activeFor($request->user(), $organization);

        return response()->json([
            'data' => $subscription !== null ? new SubscriptionResource($subscription) : null,
        ]);
    }

    public function checkout(SubscribeCheckoutRequest $request): JsonResponse
    {
        $organization = null;

        if ($request->filled('organization_id')) {
            $organization = Organization::query()->findOrFail($request->integer('organization_id'));
            $this->authorize('update', $organization);
        }

        $checkout = $this->subscriptionService->createCheckout(
            $request->user(),
            $request->validated('plan_tier'),
            $request->validated('billing_cycle'),
            $organization,
        );

        return response()->json(['data' => $checkout], 201);
    }

    public function renew(RenewSubscriptionRequest $request): JsonResponse
    {
        $organization = $this->resolveOrganization($request);

        if ($organization !== null) {
            $this->authorize('update', $organization);
        }

        $checkout = $this->subscriptionService->createRenewalCheckout(
            $request->user(),
            $organization,
        );

        return response()->json(['data' => $checkout], 201);
    }

    public function cancel(CancelSubscriptionRequest $request): SubscriptionResource
    {
        $organization = $this->resolveOrganization($request);

        if ($organization !== null) {
            $this->authorize('update', $organization);
        }

        return new SubscriptionResource(
            $this->subscriptionService->cancel($request->user(), $organization),
        );
    }

    private function resolveOrganization(Request $request): ?Organization
    {
        if (! $request->filled('organization_id')) {
            return null;
        }

        $organization = Organization::query()->findOrFail($request->integer('organization_id'));
        $this->authorize('view', $organization);

        return $organization;
    }
}
