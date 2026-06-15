<?php

namespace App\Services;

use App\Enums\DataFileFormat;
use App\Enums\VirusScanStatus;
use App\Jobs\ScanDataFileJob;
use App\Models\DataFile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DataFileUploadService
{
    private const SESSION_TTL_MINUTES = 120;

    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array{session_id: string, max_chunk_bytes: int}
     */
    public function initSession(
        User $user,
        string $originalFilename,
        int $totalSizeBytes,
        ?Organization $organization = null,
    ): array {
        $this->assertWithinSizeLimit($totalSizeBytes);

        $format = $this->resolveFormat($originalFilename);

        $sessionId = (string) Str::uuid();

        Cache::put($this->sessionCacheKey($sessionId), [
            'user_id' => $user->id,
            'organization_id' => $organization?->id,
            'original_filename' => $originalFilename,
            'format' => $format->value,
            'total_size_bytes' => $totalSizeBytes,
            'received_bytes' => 0,
            'chunks' => [],
        ], now()->addMinutes(self::SESSION_TTL_MINUTES));

        Storage::disk('local')->makeDirectory($this->tempDirectory($sessionId));

        return [
            'session_id' => $sessionId,
            'max_chunk_bytes' => 5_242_880,
        ];
    }

    public function storeChunk(string $sessionId, User $user, int $chunkIndex, UploadedFile $chunk): void
    {
        $session = $this->getSessionForUser($sessionId, $user);

        $chunkPath = $this->chunkPath($sessionId, $chunkIndex);
        Storage::disk('local')->put($chunkPath, $chunk->get());

        $session['chunks'][$chunkIndex] = Storage::disk('local')->size($chunkPath);
        $session['received_bytes'] = array_sum($session['chunks']);

        if ($session['received_bytes'] > $session['total_size_bytes']) {
            $this->abortSession($sessionId);

            throw ValidationException::withMessages([
                'chunk' => ['Uploaded data exceeds the declared file size.'],
            ]);
        }

        Cache::put($this->sessionCacheKey($sessionId), $session, now()->addMinutes(self::SESSION_TTL_MINUTES));
    }

    public function completeSession(string $sessionId, User $user, ?string $sheetName = null): DataFile
    {
        $session = $this->getSessionForUser($sessionId, $user);

        if ($session['received_bytes'] !== $session['total_size_bytes']) {
            throw ValidationException::withMessages([
                'session' => ['Upload is incomplete. All chunks must be uploaded before completing.'],
            ]);
        }

        $mergedPath = $this->mergeChunks($sessionId, $session['chunks']);
        $format = DataFileFormat::from($session['format']);
        $storagePath = $this->buildStoragePath($user->id, $session['original_filename']);

        $stream = fopen($mergedPath, 'r');

        if ($stream === false) {
            throw new RuntimeException('Unable to read merged upload file.');
        }

        Storage::disk(config('filesystems.default'))->put($storagePath, $stream);
        fclose($stream);

        $this->cleanupSessionFiles($sessionId);

        $dataFile = DataFile::query()->create([
            'user_id' => $user->id,
            'organization_id' => $session['organization_id'],
            'original_filename' => $session['original_filename'],
            's3_path' => $storagePath,
            'format' => $format,
            'file_size_bytes' => $session['total_size_bytes'],
            'sheet_name' => $sheetName,
            'virus_scan_status' => VirusScanStatus::Pending,
        ]);

        Cache::forget($this->sessionCacheKey($sessionId));

        $this->auditLogService->record('data_file.uploaded', $user, entity: $dataFile);

        return $this->scanAndRefresh($dataFile);
    }

    public function abortSession(string $sessionId): void
    {
        $this->cleanupSessionFiles($sessionId);
        Cache::forget($this->sessionCacheKey($sessionId));
    }

    public function storeDirectUpload(User $user, UploadedFile $file, ?Organization $organization = null): DataFile
    {
        $this->assertWithinSizeLimit($file->getSize());

        $format = $this->resolveFormat($file->getClientOriginalName());
        $storagePath = $this->buildStoragePath($user->id, $file->getClientOriginalName());

        Storage::disk(config('filesystems.default'))->putFileAs(
            dirname($storagePath),
            $file,
            basename($storagePath),
        );

        $dataFile = DataFile::query()->create([
            'user_id' => $user->id,
            'organization_id' => $organization?->id,
            'original_filename' => $file->getClientOriginalName(),
            's3_path' => $storagePath,
            'format' => $format,
            'file_size_bytes' => $file->getSize(),
            'virus_scan_status' => VirusScanStatus::Pending,
        ]);

        $this->auditLogService->record('data_file.uploaded', $user, entity: $dataFile);

        return $this->scanAndRefresh($dataFile);
    }

    public function delete(DataFile $dataFile, User $user): void
    {
        if ($dataFile->analysisJobs()->exists()) {
            throw ValidationException::withMessages([
                'data_file' => ['This file is linked to an analysis job and cannot be deleted.'],
            ]);
        }

        Storage::disk(config('filesystems.default'))->delete($dataFile->s3_path);
        $dataFile->delete();

        $this->auditLogService->record('data_file.deleted', $user, entity: $dataFile);
    }

    private function getSessionForUser(string $sessionId, User $user): array
    {
        $session = Cache::get($this->sessionCacheKey($sessionId));

        if (! is_array($session) || ($session['user_id'] ?? null) !== $user->id) {
            throw ValidationException::withMessages([
                'session' => ['The upload session is invalid or has expired.'],
            ]);
        }

        return $session;
    }

    private function mergeChunks(string $sessionId, array $chunks): string
    {
        ksort($chunks);

        $mergedRelativePath = $this->tempDirectory($sessionId).'/merged.dat';
        $mergedAbsolutePath = Storage::disk('local')->path($mergedRelativePath);
        $destination = fopen($mergedAbsolutePath, 'w+b');

        if ($destination === false) {
            throw new RuntimeException('Unable to create merged upload file.');
        }

        foreach (array_keys($chunks) as $chunkIndex) {
            $chunkAbsolutePath = Storage::disk('local')->path($this->chunkPath($sessionId, (int) $chunkIndex));
            $source = fopen($chunkAbsolutePath, 'r');

            if ($source === false) {
                fclose($destination);
                throw new RuntimeException('Unable to read an uploaded chunk.');
            }

            stream_copy_to_stream($source, $destination);
            fclose($source);
        }

        fclose($destination);

        return $mergedAbsolutePath;
    }

    private function cleanupSessionFiles(string $sessionId): void
    {
        Storage::disk('local')->deleteDirectory($this->tempDirectory($sessionId));
    }

    private function sessionCacheKey(string $sessionId): string
    {
        return "upload_session:{$sessionId}";
    }

    private function tempDirectory(string $sessionId): string
    {
        return "uploads/tmp/{$sessionId}";
    }

    private function chunkPath(string $sessionId, int $chunkIndex): string
    {
        return $this->tempDirectory($sessionId)."/chunk_{$chunkIndex}.part";
    }

    private function buildStoragePath(int $userId, string $originalFilename): string
    {
        $safeName = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        return sprintf(
            'data-files/%d/%s_%s.%s',
            $userId,
            now()->format('YmdHis'),
            $safeName !== '' ? $safeName : 'dataset',
            $extension,
        );
    }

    private function resolveFormat(string $filename): DataFileFormat
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $format = DataFileFormat::fromExtension($extension);

        if ($format === null) {
            throw ValidationException::withMessages([
                'filename' => ['Only .xlsx, .xls, and .csv files are supported.'],
            ]);
        }

        return $format;
    }

    private function assertWithinSizeLimit(int $sizeBytes): void
    {
        if ($sizeBytes <= 0) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded file is empty.'],
            ]);
        }

        if ($sizeBytes > config('doctorstats.max_upload_bytes')) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded file exceeds the 50 MB limit.'],
            ]);
        }
    }

    private function scanAndRefresh(DataFile $dataFile): DataFile
    {
        ScanDataFileJob::dispatchSync($dataFile);

        $dataFile = $dataFile->fresh();

        if ($dataFile === null || $dataFile->virus_scan_status === VirusScanStatus::Rejected) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded file failed validation. Check the file format and try again.'],
            ]);
        }

        return $dataFile;
    }
}
