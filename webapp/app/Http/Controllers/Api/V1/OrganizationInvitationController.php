<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInvitationRequest;
use App\Http\Resources\OrganizationInvitationResource;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationInvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    public function index(Request $request, Organization $organization): AnonymousResourceCollection
    {
        $this->authorize('manageMembers', $organization);

        $invitations = $organization->invitations()
            ->latest()
            ->get();

        return OrganizationInvitationResource::collection($invitations);
    }

    public function store(StoreInvitationRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('create', [OrganizationInvitation::class, $organization]);

        $invitation = $this->invitationService->invite(
            $organization,
            $request->user(),
            $request->validated('email'),
            OrganizationRole::from($request->validated('role')),
        );

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'invitation' => new OrganizationInvitationResource($invitation),
        ], 201);
    }

    public function destroy(
        Request $request,
        Organization $organization,
        OrganizationInvitation $invitation,
    ): JsonResponse {
        $this->authorize('delete', $invitation);

        $this->invitationService->revoke($organization, $invitation, $request->user());

        return response()->json([
            'message' => 'Invitation revoked successfully.',
        ]);
    }
}
