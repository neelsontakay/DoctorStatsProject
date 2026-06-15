<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateMemberRequest;
use App\Http\Resources\OrganizationMemberResource;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationMemberController extends Controller
{
    public function __construct(
        private readonly OrganizationService $organizationService,
    ) {}

    public function index(Request $request, Organization $organization): AnonymousResourceCollection
    {
        $this->authorize('view', $organization);

        $members = $organization->members()
            ->with('user')
            ->where('status', OrganizationMemberStatus::Active)
            ->orderBy('role')
            ->get();

        return OrganizationMemberResource::collection($members);
    }

    public function update(
        UpdateMemberRequest $request,
        Organization $organization,
        OrganizationMember $member,
    ): OrganizationMemberResource {
        $this->authorize('manageMembers', $organization);

        $member = $this->organizationService->updateMemberRole(
            $organization,
            $member,
            $request->user(),
            OrganizationRole::from($request->validated('role')),
        );

        return new OrganizationMemberResource($member);
    }

    public function destroy(
        Request $request,
        Organization $organization,
        OrganizationMember $member,
    ): JsonResponse {
        $this->authorize('manageMembers', $organization);

        $this->organizationService->removeMember(
            $organization,
            $member,
            $request->user(),
        );

        return response()->json([
            'message' => 'Member removed successfully.',
        ]);
    }
}
