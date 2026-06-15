<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UnlockSharedReportRequest;
use App\Http\Resources\ReportResource;
use App\Services\ReportExportService;
use App\Services\ReportShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SharedReportController extends Controller
{
    public function __construct(
        private readonly ReportShareService $reportShareService,
        private readonly ReportExportService $reportExportService,
    ) {}

    public function show(Request $request, string $token): JsonResponse|StreamedResponse
    {
        $share = $this->reportShareService->findAccessibleShare($token);

        if ($share->password_hash !== null && ! Cache::get($this->unlockCacheKey($token), false)) {
            return response()->json([
                'message' => 'Password required.',
                'password_required' => true,
            ], 401);
        }

        $report = $share->report;

        if ($request->query('format') === 'html') {
            $path = $this->reportExportService->resolveDownloadPath($report, 'html');

            return Storage::disk(config('filesystems.default'))->response($path, 'report.html', [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        return response()->json([
            'data' => [
                'report' => new ReportResource($report->load('analysisJob')),
                'share_method' => $share->share_method->value,
                'expires_at' => $share->expires_at?->toIso8601String(),
            ],
        ]);
    }

    public function unlock(UnlockSharedReportRequest $request, string $token): JsonResponse
    {
        $share = $this->reportShareService->findAccessibleShare($token);
        $this->reportShareService->verifyPassword($share, $request->validated('password'));

        Cache::put(
            $this->unlockCacheKey($token),
            true,
            $share->expires_at ?? now()->addDay(),
        );

        return response()->json([
            'message' => 'Share unlocked successfully.',
        ]);
    }

    private function unlockCacheKey(string $token): string
    {
        return "shared_report_unlocked.{$token}";
    }
}
