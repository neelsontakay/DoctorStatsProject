<?php

namespace Tests\Feature\Api;

use App\Enums\AnalysisJobStatus;
use App\Enums\ReportStatus;
use App\Mail\AnalysisCompleteMail;
use App\Models\AnalysisJob;
use App\Models\DataFile;
use App\Models\Report;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionalEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_analysis_complete_notification_sends_email(): void
    {
        Mail::fake();
        Config::set('services.zeptomail.api_key', '');

        $user = User::factory()->create();
        $dataFile = DataFile::factory()->create(['user_id' => $user->id]);

        $job = AnalysisJob::query()->create([
            'job_id' => 'DS-2026-EMAILTEST1',
            'user_id' => $user->id,
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'status' => AnalysisJobStatus::Completed,
            'access_scope' => 'private',
            'payment_method' => 'pay_per_job',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);

        $report = Report::query()->create([
            'analysis_job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Email test report',
            'status' => ReportStatus::Published,
        ]);

        app(NotificationService::class)->analysisCompleted($job, $report);

        Mail::assertSent(AnalysisCompleteMail::class, function (AnalysisCompleteMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
    }

    public function test_zeptomail_service_sends_html_when_configured(): void
    {
        Config::set('services.zeptomail.api_key', 'test-key');
        Config::set('services.zeptomail.api_url', 'https://api.zeptomail.com/v1.1');

        Http::fake([
            'https://api.zeptomail.com/v1.1/email' => Http::response(['data' => ['message' => 'OK']], 200),
        ]);

        app(\App\Services\ZeptoMailService::class)->sendHtml(
            'doctor@example.com',
            'Doctor',
            'Test subject',
            '<p>Hello</p>',
        );

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.zeptomail.com/v1.1/email'
                && $request['subject'] === 'Test subject';
        });
    }
}
