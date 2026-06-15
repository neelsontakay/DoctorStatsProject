<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly OrganizationService $organizationService,
    ) {}

    public function show(Request $request, Organization $organization): OrganizationResource
    {
        $this->authorize('view', $organization);

        return new OrganizationResource($organization);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): OrganizationResource
    {
        $this->authorize('update', $organization);

        $organization = $this->organizationService->update(
            $organization,
            $request->user(),
            $request->validated(),
        );

        return new OrganizationResource($organization);
    }

    public function analytics(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('viewAnalytics', $organization);

        return response()->json([
            'data' => $this->organizationService->analytics($organization),
        ]);
    }
}
