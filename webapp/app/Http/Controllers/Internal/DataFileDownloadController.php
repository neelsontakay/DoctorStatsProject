<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\DataFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataFileDownloadController extends Controller
{
    public function show(DataFile $dataFile): StreamedResponse
    {
        $disk = Storage::disk(config('filesystems.default'));

        if (! $disk->exists($dataFile->s3_path)) {
            abort(404, 'Data file not found in storage.');
        }

        return $disk->response($dataFile->s3_path, $dataFile->original_filename);
    }
}
