<?php

namespace Tests\Feature\Api;

use App\Enums\NotificationType;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_and_mark_notifications_read(): void
    {
        $user = User::factory()->create();

        $notification = UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => NotificationType::AnalysisComplete,
            'title' => 'Analysis completed',
            'message' => 'Your report is ready.',
            'data' => ['report_id' => 1],
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/notifications?unread=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $notification->id);

        $readResponse = $this->actingAs($user)
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $readResponse->assertOk()
            ->assertJsonPath('read_at', fn ($value) => $value !== null);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);

        $this->actingAs($user)
            ->postJson('/api/v1/notifications/read-all')
            ->assertOk();
    }
}
