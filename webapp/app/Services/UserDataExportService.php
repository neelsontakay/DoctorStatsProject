<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;

class UserDataExportService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function export(User $user): array
    {
        $user->load([
            'organizationMemberships.organization',
            'analysisJobs.columns',
            'analysisJobs.results',
            'subscriptions',
            'auditLogs',
        ]);

        $this->auditLogService->record('user.data_exported', $user, entity: $user);

        return [
            'exported_at' => Carbon::now()->toIso8601String(),
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_type' => $user->account_type->value,
                'status' => $user->status->value,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'organization_memberships' => $user->organizationMemberships->map(fn ($membership) => [
                'organization_id' => $membership->organization_id,
                'organization_name' => $membership->organization?->name,
                'role' => $membership->role->value,
                'status' => $membership->status->value,
            ])->values()->all(),
            'analysis_jobs' => $user->analysisJobs->map(fn ($job) => [
                'job_id' => $job->job_id,
                'status' => $job->status->value,
                'objectives' => $job->objectives,
                'access_scope' => $job->access_scope,
                'payment_method' => $job->payment_method,
                'submitted_at' => $job->submitted_at?->toIso8601String(),
                'completed_at' => $job->completed_at?->toIso8601String(),
                'columns' => $job->columns->map(fn ($column) => [
                    'column_name' => $column->column_name,
                    'data_type' => $column->data_type->value,
                    'variable_type' => $column->variable_type->value,
                    'description' => $column->description,
                ])->values()->all(),
                'results_count' => $job->results->count(),
            ])->values()->all(),
            'subscriptions' => $user->subscriptions->map(fn ($subscription) => [
                'plan_tier' => $subscription->plan_tier,
                'billing_cycle' => $subscription->billing_cycle,
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at?->toIso8601String(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
            ])->values()->all(),
            'payments' => $user->payments()
                ->latest()
                ->get(['amount', 'currency', 'status', 'payment_type', 'paid_at', 'created_at'])
                ->map(fn ($payment) => [
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status->value,
                    'payment_type' => $payment->payment_type->value,
                    'paid_at' => $payment->paid_at?->toIso8601String(),
                    'created_at' => $payment->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'notifications' => $user->notifications()
                ->latest()
                ->limit(100)
                ->get(['type', 'title', 'message', 'read_at', 'created_at'])
                ->map(fn ($notification) => [
                    'type' => $notification->type->value,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'audit_logs' => $user->auditLogs
                ->take(100)
                ->map(fn ($log) => [
                    'action' => $log->action,
                    'created_at' => $log->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
        ];
    }
}
