<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportFolderRequest;
use App\Http\Requests\Api\V1\UpdateReportFolderRequest;
use App\Http\Resources\ReportFolderResource;
use App\Models\Organization;
use App\Models\ReportFolder;
use App\Services\ReportLibraryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReportFolderController extends Controller
{
    public function __construct(
        private readonly ReportLibraryService $reportLibraryService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ReportFolder::class);

        $organization = null;

        if ($request->filled('organization_id')) {
            $organization = Organization::query()->findOrFail($request->integer('organization_id'));
            $this->authorize('view', $organization);
        }

        $folders = $this->reportLibraryService
            ->foldersQuery($request->user(), $organization)
            ->withCount('reports')
            ->get();

        return ReportFolderResource::collection($folders);
    }

    public function store(StoreReportFolderRequest $request): ReportFolderResource
    {
        $this->authorize('create', ReportFolder::class);

        $organization = null;

        if ($request->filled('organization_id')) {
            $organization = Organization::query()->findOrFail($request->integer('organization_id'));
            $this->authorize('view', $organization);
        }

        $folder = $this->reportLibraryService->createFolder(
            $request->user(),
            $request->validated(),
            $organization,
        );

        return new ReportFolderResource($folder);
    }

    public function update(UpdateReportFolderRequest $request, ReportFolder $reportFolder): ReportFolderResource
    {
        $this->authorize('update', $reportFolder);

        $folder = $this->reportLibraryService->updateFolder($reportFolder, $request->validated());

        return new ReportFolderResource($folder);
    }

    public function destroy(ReportFolder $reportFolder): Response
    {
        $this->authorize('delete', $reportFolder);

        $this->reportLibraryService->deleteFolder($reportFolder);

        return response()->noContent();
    }
}
