<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppController extends Controller
{
    public function home(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('welcome');
    }

    public function login(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function register(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function forgotPassword(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function resetPassword(Request $request, ?string $token = null): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token ?? $request->query('token', ''),
            'email' => $request->query('email', ''),
        ]);
    }

    public function dashboard(): View
    {
        return view('app.dashboard', ['activeNav' => 'Dashboard']);
    }

    public function analysesIndex(): View
    {
        return view('app.analyses.index', ['activeNav' => 'Analyses']);
    }

    public function analysesCreate(): View
    {
        return view('app.analyses.create');
    }

    public function analysesShow(string $analysisJob): View
    {
        return view('app.analyses.show', [
            'activeNav' => 'Analyses',
            'jobId' => $analysisJob,
        ]);
    }

    public function demoAnalysis(): View
    {
        return view('app.demo-analysis', ['activeNav' => 'Analyses']);
    }

    public function reportsIndex(): View
    {
        return view('app.reports.index', ['activeNav' => 'Reports']);
    }

    public function reportsShow(int $report): View
    {
        return view('app.reports.show', [
            'activeNav' => 'Reports',
            'reportId' => $report,
        ]);
    }

    public function profile(): View
    {
        return view('app.profile', ['activeNav' => 'Dashboard']);
    }

    public function organization(): View
    {
        return view('app.organization', ['activeNav' => 'Org']);
    }

    public function acceptInvitation(string $token): View
    {
        return view('app.invitation', ['token' => $token]);
    }
}
