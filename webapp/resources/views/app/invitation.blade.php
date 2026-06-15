@extends('layouts.auth')

@section('title', 'Accept invitation — DoctorStats')

@section('content')
    <div class="card w-full max-w-md" x-data="invitationAcceptPage(@js($token))">
        <div class="card-pad text-center">
            <h1 class="text-2xl font-bold text-foreground">Organization invitation</h1>
            <p class="mt-2 text-sm text-muted-foreground">Accept this invitation to join your team's workspace.</p>

            <p x-show="message" x-text="message" class="alert-info mt-4"></p>
            <p x-show="error" x-text="error" class="alert-error mt-4"></p>

            <button type="button" class="btn btn-primary btn-lg w-full mt-6" @click="accept" :disabled="loading">
                <span x-show="!loading">Accept invitation</span>
                <span x-show="loading">Accepting...</span>
            </button>

            <p class="text-sm text-muted-foreground mt-6">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Go to dashboard</a>
            </p>
        </div>
    </div>
@endsection
