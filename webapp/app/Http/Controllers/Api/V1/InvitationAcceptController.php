<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationMemberResource;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationAcceptController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    public function store(Request $request, string $token): JsonResponse
    {
        $member = $this->invitationService->accept($token, $request->user());

        return response()->json([
            'message' => 'Invitation accepted successfully.',
            'membership' => new OrganizationMemberResource($member),
        ]);
    }
}
