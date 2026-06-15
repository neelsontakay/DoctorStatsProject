<?php

use App\Http\Controllers\Api\V1\AnalysisJobController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DataFileController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InvitationAcceptController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\OrganizationInvitationController;
use App\Http\Controllers\Api\V1\OrganizationMemberController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ReportFolderController;
use App\Http\Controllers\Api\V1\ReportShareController;
use App\Http\Controllers\Api\V1\ReportTagController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\SharedReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('shared/{token}', [SharedReportController::class, 'show']);
    Route::post('shared/{token}/unlock', [SharedReportController::class, 'unlock']);

    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [ProfileController::class, 'show']);
        Route::patch('me', [ProfileController::class, 'update']);
        Route::patch('me/password', [ProfileController::class, 'changePassword']);
        Route::delete('me', [ProfileController::class, 'destroy']);
        Route::get('me/activity', [ProfileController::class, 'activity']);
        Route::get('me/export', [ProfileController::class, 'export']);

        Route::get('dashboard', [DashboardController::class, 'show']);

        Route::middleware('throttle:upload')->group(function (): void {
            Route::post('data-files/upload/init', [DataFileController::class, 'init']);
            Route::post('data-files/upload/{session}/chunk', [DataFileController::class, 'chunk']);
            Route::post('data-files/upload/{session}/complete', [DataFileController::class, 'complete']);
            Route::delete('data-files/upload/{session}', [DataFileController::class, 'abort']);
            Route::post('data-files', [DataFileController::class, 'store']);
        });

        Route::get('data-files/{dataFile}', [DataFileController::class, 'show']);
        Route::get('data-files/{dataFile}/preview', [DataFileController::class, 'preview']);
        Route::get('data-files/{dataFile}/sheets', [DataFileController::class, 'sheets']);
        Route::delete('data-files/{dataFile}', [DataFileController::class, 'destroy']);

        Route::get('analysis-jobs', [AnalysisJobController::class, 'index']);
        Route::post('analysis-jobs', [AnalysisJobController::class, 'store']);
        Route::get('analysis-jobs/{analysisJob}', [AnalysisJobController::class, 'show']);
        Route::get('analysis-jobs/{analysisJob}/results', [AnalysisJobController::class, 'results']);
        Route::get('analysis-jobs/{analysisJob}/status', [AnalysisJobController::class, 'status']);
        Route::patch('analysis-jobs/{analysisJob}/access', [AnalysisJobController::class, 'updateAccess']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/{userNotification}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::get('payments', [PaymentController::class, 'index']);
        Route::get('payments/quote', [PaymentController::class, 'quote']);
        Route::post('payments/checkout', [PaymentController::class, 'checkout']);
        Route::post('payments/{payment}/stub-confirm', [PaymentController::class, 'stubConfirm']);

        Route::get('subscriptions/plans', [SubscriptionController::class, 'plans']);
        Route::get('subscriptions/quote', [SubscriptionController::class, 'quote']);
        Route::get('subscriptions/current', [SubscriptionController::class, 'current']);
        Route::post('subscriptions/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('subscriptions/renew', [SubscriptionController::class, 'renew']);
        Route::post('subscriptions/cancel', [SubscriptionController::class, 'cancel']);

        Route::get('report-folders', [ReportFolderController::class, 'index']);
        Route::post('report-folders', [ReportFolderController::class, 'store']);
        Route::patch('report-folders/{reportFolder}', [ReportFolderController::class, 'update']);
        Route::delete('report-folders/{reportFolder}', [ReportFolderController::class, 'destroy']);

        Route::get('report-tags', [ReportTagController::class, 'index']);
        Route::post('report-tags', [ReportTagController::class, 'store']);
        Route::patch('report-tags/{reportTag}', [ReportTagController::class, 'update']);
        Route::delete('report-tags/{reportTag}', [ReportTagController::class, 'destroy']);

        Route::get('reports', [ReportController::class, 'index']);
        Route::get('reports/{report}', [ReportController::class, 'show']);
        Route::patch('reports/{report}', [ReportController::class, 'update']);
        Route::get('reports/{report}/view', [ReportController::class, 'view']);
        Route::get('reports/{report}/download/{format}', [ReportController::class, 'download'])
            ->where('format', 'html|pdf|excel');
        Route::get('reports/{report}/shares', [ReportShareController::class, 'index']);
        Route::post('reports/{report}/shares', [ReportShareController::class, 'store']);
        Route::delete('reports/{report}/shares/{share}', [ReportShareController::class, 'destroy']);

        Route::post('invitations/{token}/accept', [InvitationAcceptController::class, 'store']);

        Route::middleware('org.member')->group(function (): void {
            Route::get('organizations/{organization}', [OrganizationController::class, 'show']);
            Route::get('organizations/{organization}/analytics', [OrganizationController::class, 'analytics']);
            Route::get('organizations/{organization}/members', [OrganizationMemberController::class, 'index']);
        });

        Route::middleware('org.admin')->group(function (): void {
            Route::patch('organizations/{organization}', [OrganizationController::class, 'update']);
            Route::patch('organizations/{organization}/members/{member}', [OrganizationMemberController::class, 'update']);
            Route::delete('organizations/{organization}/members/{member}', [OrganizationMemberController::class, 'destroy']);
            Route::get('organizations/{organization}/invitations', [OrganizationInvitationController::class, 'index']);
            Route::post('organizations/{organization}/invitations', [OrganizationInvitationController::class, 'store']);
            Route::delete('organizations/{organization}/invitations/{invitation}', [OrganizationInvitationController::class, 'destroy']);
        });
    });

    Route::middleware(['auth:sanctum', 'signed'])->group(function (): void {
        Route::get('auth/email/verify/{id}/{hash}', [ProfileController::class, 'verifyEmail'])
            ->name('verification.verify');
    });
});
