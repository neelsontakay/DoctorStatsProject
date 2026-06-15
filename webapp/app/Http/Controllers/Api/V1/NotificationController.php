<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserNotificationResource;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()
            ->notifications()
            ->when($request->boolean('unread'), fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(20);

        return UserNotificationResource::collection($notifications);
    }

    public function markRead(Request $request, UserNotification $userNotification): UserNotificationResource
    {
        if ($userNotification->user_id !== $request->user()->id) {
            abort(403);
        }

        return new UserNotificationResource(
            $this->notificationService->markRead($userNotification),
        );
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllRead($request->user());

        return response()->json([
            'message' => 'Notifications marked as read.',
            'updated_count' => $count,
        ]);
    }
}
