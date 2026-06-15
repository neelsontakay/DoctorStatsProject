@extends('layouts.shell', ['activeNav' => 'Dashboard'])

@section('title', 'Profile — DoctorStats')

@section('content')
    <div x-data="profilePage" x-init="init">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">Profile</h1>
            <p class="text-muted-foreground">Manage your account settings</p>
        </div>

        <template x-if="loading">
            <p class="text-muted-foreground">Loading profile...</p>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl" x-show="!loading">
            <div class="card card-pad">
                <h2 class="text-lg font-semibold text-foreground mb-1">Personal information</h2>
                <p class="text-sm text-muted-foreground mb-4">Update your profile details</p>
                <p x-show="message" x-text="message" class="alert-info mb-4"></p>
                <p x-show="error" x-text="error" class="alert-error mb-4"></p>
                <form class="space-y-4" @submit.prevent="saveProfile">
                    <div>
                        <label for="profile-name" class="label">Full name</label>
                        <input id="profile-name" type="text" x-model="name" class="input" :disabled="saving">
                    </div>
                    <div>
                        <label for="profile-email" class="label">Email</label>
                        <input id="profile-email" type="email" x-model="email" class="input" disabled>
                        <p class="text-xs text-muted-foreground mt-1">Contact support to change your email</p>
                    </div>
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        <span x-show="!saving">Save changes</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </form>
            </div>

            <div class="space-y-8">
                <div class="card card-pad">
                    <h2 class="text-lg font-semibold text-foreground mb-4">Change password</h2>
                    <p x-show="passwordMessage" x-text="passwordMessage" class="alert-info mb-4"></p>
                    <p x-show="passwordError" x-text="passwordError" class="alert-error mb-4"></p>
                    <form class="space-y-4" @submit.prevent="savePassword">
                        <div>
                            <label class="label">Current password</label>
                            <input type="password" x-model="currentPassword" class="input" :disabled="passwordSaving">
                        </div>
                        <div>
                            <label class="label">New password</label>
                            <input type="password" x-model="newPassword" class="input" :disabled="passwordSaving">
                        </div>
                        <div>
                            <label class="label">Confirm new password</label>
                            <input type="password" x-model="newPasswordConfirmation" class="input" :disabled="passwordSaving">
                        </div>
                        <button type="submit" class="btn btn-outline" :disabled="passwordSaving">Update password</button>
                    </form>
                </div>

                <div class="card card-pad border-destructive/30">
                    <h2 class="text-lg font-semibold text-destructive mb-1">Danger zone</h2>
                    <p class="text-sm text-muted-foreground mb-4">Permanently deactivate your account and personal data</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ url('/api/v1/me/export') }}" class="btn btn-outline">Export my data</a>
                        <button type="button" class="btn btn-destructive" @click="deactivate">Deactivate account</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
