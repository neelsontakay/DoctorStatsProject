@extends('layouts.auth')

@section('title', 'Sign in — DoctorStats')

@section('content')
    <div class="card w-full max-w-md" x-data="loginForm">
        <div class="card-pad">
            <h1 class="text-2xl font-bold text-foreground">Sign in</h1>
            <p class="mt-1 text-sm text-muted-foreground">Access your analyses and reports</p>

            @if (request('registered'))
                <div class="alert-info mt-4">Registration successful. Please verify your email, then sign in.</div>
            @endif
            @if (request('reset'))
                <div class="alert-info mt-4">Password reset successful. You can sign in now.</div>
            @endif

            <div class="mt-4 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm">
                <p class="font-semibold text-primary-900 mb-1">Demo credentials</p>
                <p class="text-primary-800"><span class="text-primary-700">Email:</span> <code class="font-mono text-xs bg-primary-100 px-1.5 py-0.5 rounded">test@example.com</code></p>
                <p class="text-primary-800 mt-1"><span class="text-primary-700">Password:</span> <code class="font-mono text-xs bg-primary-100 px-1.5 py-0.5 rounded">password</code></p>
            </div>

            <p x-show="error" x-text="error" class="alert-error mt-4"></p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label for="email" class="label">Email <span class="text-destructive">*</span></label>
                    <input id="email" type="email" x-model="email" required class="input" placeholder="you@institution.edu" autocomplete="email" :disabled="loading">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="label mb-0">Password <span class="text-destructive">*</span></label>
                        <a href="{{ route('password.request') }}" class="text-xs text-primary hover:underline">Forgot password?</a>
                    </div>
                    <input id="password" type="password" x-model="password" required class="input" autocomplete="current-password" :disabled="loading">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="loading">
                    <span x-show="!loading">Sign in</span>
                    <span x-show="loading">Signing in...</span>
                </button>
                <button type="button" class="btn btn-outline btn-lg w-full" @click="demoLogin" :disabled="loading">Demo sign in</button>
            </form>

            <p class="text-sm text-muted-foreground text-center mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-primary font-medium hover:underline">Create account</a>
            </p>
        </div>
    </div>
@endsection
