<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CompleteUploadRequest;
use App\Http\Requests\Api\V1\InitUploadRequest;
use App\Http\Requests\Api\V1\StoreDataFileRequest;
use App\Http\Requests\Api\V1\UploadChunkRequest;
use App\Http\Resources\DataFileResource;
use App\Enums\VirusScanStatus;
use App\Models\DataFile;
use App\Models\Organization;
use App\Services\DataFileUploadService;
use App\Services\SpreadsheetReaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataFileController extends Controller
{
    public function __construct(
        private readonly DataFileUploadService $uploadService,
        private readonly SpreadsheetReaderService $spreadsheetReaderService,
    ) {}

    public function init(InitUploadRequest $request): JsonResponse
    {
        $organization = $this->resolveOrganization($request);

        $session = $this->uploadService->initSession(
            $request->user(),
            $request->validated('filename'),
            $request->integer('total_size_bytes'),
            $organization,
        );

        return response()->json(['data' => $session], 201);
    }

    public function chunk(UploadChunkRequest $request, string $session): JsonResponse
    {
        $this->uploadService->storeChunk(
            $session,
            $request->user(),
            $request->integer('chunk_index'),
            $request->file('chunk'),
        );

        return response()->json([
            'message' => 'Chunk uploaded successfully.',
        ]);
    }

    public function complete(CompleteUploadRequest $request, string $session): JsonResponse
    {
        $dataFile = $this->uploadService->completeSession(
            $session,
            $request->user(),
            $request->validated('sheet_name'),
        )->fresh();

        return response()->json([
            'message' => 'Upload completed successfully.',
            'data_file' => new DataFileResource($dataFile),
        ], 201);
    }

    public function abort(Request $request, string $session): JsonResponse
    {
        $this->uploadService->abortSession($session);

        return response()->json([
            'message' => 'Upload session cancelled.',
        ]);
    }

    public function store(StoreDataFileRequest $request): JsonResponse
    {
        $organization = $this->resolveOrganization($request);

        $dataFile = $this->uploadService->storeDirectUpload(
            $request->user(),
            $request->file('file'),
            $organization,
        )->fresh();

        return response()->json([
            'message' => 'File uploaded successfully.',
            'data_file' => new DataFileResource($dataFile),
        ], 201);
    }

    public function show(Request $request, DataFile $dataFile): DataFileResource
    {
        $this->authorize('view', $dataFile);

        return new DataFileResource($dataFile);
    }

    public function preview(Request $request, DataFile $dataFile): JsonResponse
    {
        $this->authorize('view', $dataFile);

        if ($dataFile->virus_scan_status === VirusScanStatus::Rejected) {
            abort(422, 'This file failed validation and cannot be previewed.');
        }

        $preview = $this->spreadsheetReaderService->preview(
            $dataFile,
            $request->query('sheet_name', $dataFile->sheet_name),
        );

        return response()->json(['data' => $preview]);
    }

    public function sheets(Request $request, DataFile $dataFile): JsonResponse
    {
        $this->authorize('view', $dataFile);

        return response()->json([
            'data' => $this->spreadsheetReaderService->sheetNames($dataFile),
        ]);
    }

    public function destroy(Request $request, DataFile $dataFile): JsonResponse
    {
        $this->authorize('delete', $dataFile);

        $this->uploadService->delete($dataFile, $request->user());

        return response()->json([
            'message' => 'Data file deleted successfully.',
        ]);
    }

    private function resolveOrganization(Request $request): ?Organization
    {
        $organizationId = $request->input('organization_id');

        if ($organizationId === null) {
            return null;
        }

        $organization = Organization::query()->findOrFail($organizationId);

        if (! $request->user()->isMemberOf($organization)) {
            abort(403, 'You are not a member of this organization.');
        }

        return $organization;
    }
}
