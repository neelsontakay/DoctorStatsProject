<?php

use App\Http\Controllers\Web\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AppController::class, 'home'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AppController::class, 'login'])->name('login');
    Route::get('/register', [AppController::class, 'register'])->name('register');
    Route::get('/forgot-password', [AppController::class, 'forgotPassword'])->name('password.request');
    Route::get('/reset-password/{token?}', [AppController::class, 'resetPassword'])->name('password.reset');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [AppController::class, 'dashboard'])->name('dashboard');
    Route::get('/analyses', [AppController::class, 'analysesIndex'])->name('analyses.index');
    Route::get('/analyses/create', [AppController::class, 'analysesCreate'])->name('analyses.create');
    Route::get('/analyses/{analysisJob}', [AppController::class, 'analysesShow'])->name('analyses.show');
    Route::get('/analysis/demo', [AppController::class, 'demoAnalysis'])->name('analysis.demo');
    Route::get('/reports', [AppController::class, 'reportsIndex'])->name('reports.index');
    Route::get('/reports/{report}', [AppController::class, 'reportsShow'])->name('reports.show');
    Route::get('/profile', [AppController::class, 'profile'])->name('profile');
    Route::get('/organization', [AppController::class, 'organization'])->name('organization');
    Route::get('/invitations/{token}', [AppController::class, 'acceptInvitation'])->name('invitations.accept');
});
