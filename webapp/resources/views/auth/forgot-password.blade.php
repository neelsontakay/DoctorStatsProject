@extends('layouts.auth')

@section('title', 'Forgot password — DoctorStats')

@section('content')
    <div class="card w-full max-w-md" x-data="forgotPasswordForm">
        <div class="card-pad">
            <h1 class="text-2xl font-bold text-foreground">Forgot password</h1>
            <p class="mt-1 text-sm text-muted-foreground">We'll email you a reset link if an account exists</p>

            <p x-show="message" x-text="message" class="alert-info mt-4"></p>
            <p x-show="error" x-text="error" class="alert-error mt-4"></p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" x-model="email" required class="input" :disabled="loading">
                </div>
                <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                    <span x-show="!loading">Send reset link</span>
                    <span x-show="loading">Sending...</span>
                </button>
            </form>

            <p class="text-sm text-muted-foreground text-center mt-6">
                <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Back to sign in</a>
            </p>
        </div>
    </div>
@endsection
