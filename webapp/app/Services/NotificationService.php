<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Mail\AnalysisCompleteMail;
use App\Models\AnalysisJob;
use App\Models\Report;
use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function __construct(
        private readonly TransactionalEmailService $transactionalEmailService,
    ) {}
    public function notify(
        User $user,
        NotificationType $type,
        string $title,
        string $message,
        ?array $data = null,
    ): UserNotification {
        return UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function analysisCompleted(AnalysisJob $job, Report $report): void
    {
        $this->notify(
            $job->user,
            NotificationType::AnalysisComplete,
            'Analysis completed',
            "Your analysis {$job->job_id} has completed and the report is ready to view.",
            [
                'job_id' => $job->job_id,
                'report_id' => $report->id,
            ],
        );

        $this->transactionalEmailService->sendMailable(
            $job->user->email,
            $job->user->name,
            new AnalysisCompleteMail($job, $report),
        );
    }

    public function reportShared(Report $report, User $recipient, User $sharedBy): void
    {
        $this->notify(
            $recipient,
            NotificationType::Share,
            'Report shared with you',
            "{$sharedBy->name} shared the report \"{$report->title}\" with you.",
            [
                'report_id' => $report->id,
                'shared_by' => $sharedBy->id,
            ],
        );
    }

    public function markRead(UserNotification $notification): UserNotification
    {
        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return $notification->fresh();
    }

    public function markAllRead(User $user): int
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
