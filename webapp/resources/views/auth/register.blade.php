@extends('layouts.auth')

@section('title', 'Create account — DoctorStats')

@section('content')
    <div class="card w-full max-w-md" x-data="registerForm">
        <div class="card-pad">
            <h1 class="text-2xl font-bold text-foreground">Create account</h1>
            <p class="mt-1 text-sm text-muted-foreground">Start analyzing clinical data in minutes</p>

            <p x-show="error" x-text="error" class="alert-error mt-4"></p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label for="name" class="label">Full name <span class="text-destructive">*</span></label>
                    <input id="name" type="text" x-model="name" required class="input" :disabled="loading">
                </div>
                <div>
                    <label for="email" class="label">Email <span class="text-destructive">*</span></label>
                    <input id="email" type="email" x-model="email" required class="input" :disabled="loading">
                </div>
                <div>
                    <label for="password" class="label">Password <span class="text-destructive">*</span></label>
                    <input id="password" type="password" x-model="password" required class="input" :disabled="loading">
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirm password <span class="text-destructive">*</span></label>
                    <input id="password_confirmation" type="password" x-model="password_confirmation" required class="input" :disabled="loading">
                </div>
                <div>
                    <label class="label">Account type</label>
                    <select x-model="account_type" class="input" :disabled="loading">
                        <option value="individual">Individual researcher</option>
                        <option value="organizational">Organization / lab</option>
                    </select>
                </div>
                <div x-show="account_type === 'organizational'">
                    <label for="organization_name" class="label">Organization name <span class="text-destructive">*</span></label>
                    <input id="organization_name" type="text" x-model="organization_name" class="input" :disabled="loading">
                </div>
                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" x-model="accepted_terms" class="mt-1" :disabled="loading">
                    <span>I accept the terms of service and privacy policy</span>
                </label>
                <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="loading || !accepted_terms">
                    <span x-show="!loading">Create account</span>
                    <span x-show="loading">Creating account...</span>
                </button>
            </form>

            <p class="text-sm text-muted-foreground text-center mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Sign in</a>
            </p>
        </div>
    </div>
@endsection
