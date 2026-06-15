<?php

use App\Http\Controllers\Internal\AnalysisCallbackController;
use App\Http\Controllers\Internal\DataFileDownloadController;
use Illuminate\Support\Facades\Route;

Route::post('analysis-callback', [AnalysisCallbackController::class, 'store']);
Route::get('data-files/{dataFile}/download', [DataFileDownloadController::class, 'show']);
