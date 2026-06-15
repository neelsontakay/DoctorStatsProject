@extends('layouts.auth')

@section('title', 'Reset password — DoctorStats')

@section('content')
    <div class="card w-full max-w-md" x-data="resetPasswordForm(@js($token), @js($email))">
        <div class="card-pad">
            <h1 class="text-2xl font-bold text-foreground">Reset password</h1>
            <p class="mt-1 text-sm text-muted-foreground">Choose a new password for your account</p>

            <p x-show="error" x-text="error" class="alert-error mt-4"></p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" x-model="email" required class="input" :disabled="loading">
                </div>
                <div>
                    <label for="password" class="label">New password</label>
                    <input id="password" type="password" x-model="password" required class="input" :disabled="loading">
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirm password</label>
                    <input id="password_confirmation" type="password" x-model="password_confirmation" required class="input" :disabled="loading">
                </div>
                <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                    <span x-show="!loading">Reset password</span>
                    <span x-show="loading">Resetting...</span>
                </button>
            </form>
        </div>
    </div>
@endsection
