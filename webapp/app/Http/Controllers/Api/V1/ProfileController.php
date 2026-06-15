<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\UserResource;
use App\Services\AccountService;
use App\Services\UserDataExportService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly UserDataExportService $userDataExportService,
    ) {}

    public function show(Request $request): UserResource
    {
        $user = $request->user()->load('organizationMemberships.organization');

        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $this->accountService->updateProfile(
            $request->user(),
            $request->validated(),
        );

        return new UserResource($user->load('organizationMemberships.organization'));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->accountService->changePassword(
            $request->user(),
            $request->validated('password'),
        );

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->accountService->deactivate($request->user());

        return response()->json([
            'message' => 'Account deactivated successfully.',
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $payload = $this->userDataExportService->export($request->user());

        return response()->json($payload, 200, [
            'Content-Disposition' => 'attachment; filename="doctorstats-export-'.$request->user()->id.'.json"',
        ]);
    }

    public function activity(Request $request): AnonymousResourceCollection
    {
        $logs = $request->user()
            ->auditLogs()
            ->latest('created_at')
            ->paginate(20);

        return AuditLogResource::collection($logs);
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $user = $this->accountService->markEmailVerified($request->user());

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => new UserResource($user->load('organizationMemberships.organization')),
        ]);
    }
}
