@extends('layouts.shell', ['activeNav' => 'Org'])

@section('title', 'Organization — DoctorStats')

@section('content')
    <div x-data="organizationPage" x-init="init">
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-foreground mb-2">Organization</h1>
                <p class="text-muted-foreground">Manage team members and collaboration settings</p>
            </div>
            <button type="button" class="btn btn-primary" x-show="isAdmin() && organization" @click="$refs.inviteDialog.showModal()">Invite member</button>
        </div>

        <template x-if="loading">
            <p class="text-muted-foreground">Loading organization...</p>
        </template>

        <template x-if="!loading && error">
            <p class="alert-error" x-text="error"></p>
        </template>

        <template x-if="!loading && !error && !organization">
            <div class="card card-pad text-center py-12">
                <p class="font-medium text-foreground mb-2">No organization workspace</p>
                <p class="text-sm text-muted-foreground">Organization features are available on organizational accounts.</p>
            </div>
        </template>

        <template x-if="!loading && organization">
            <div class="card">
                <div class="card-pad border-b border-border">
                    <h2 class="text-lg font-semibold text-foreground" x-text="organization.name"></h2>
                    <p class="text-sm text-muted-foreground mt-1" x-text="`${members.length} active members`"></p>
                </div>
                <div class="card-pad overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left text-muted-foreground">
                                <th class="py-2 pr-4">Member</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Role</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="member in members" :key="member.id">
                                <tr class="border-b border-border">
                                    <td class="py-3 pr-4 font-medium" x-text="member.user?.name ?? '—'"></td>
                                    <td class="py-3 pr-4" x-text="member.user?.email ?? '—'"></td>
                                    <td class="py-3 pr-4 capitalize" x-text="member.role"></td>
                                    <td class="py-3 capitalize" x-text="member.status"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <dialog x-ref="inviteDialog" class="rounded-xl border border-border p-0 backdrop:bg-black/50 max-w-md w-full">
            <div class="card-pad">
                <h3 class="text-lg font-semibold text-foreground mb-1">Invite team member</h3>
                <p class="text-sm text-muted-foreground mb-4" x-text="organization ? `Send an invitation to join ${organization.name}` : ''"></p>
                <p x-show="inviteMessage" x-text="inviteMessage" class="alert-info mb-4"></p>
                <p x-show="inviteError" x-text="inviteError" class="alert-error mb-4"></p>
                <form class="space-y-4" @submit.prevent="invite(); $refs.inviteDialog.close()">
                    <div>
                        <label class="label">Email address</label>
                        <input type="email" x-model="inviteEmail" required class="input" placeholder="colleague@institution.edu">
                    </div>
                    <div>
                        <label class="label">Role</label>
                        <select x-model="inviteRole" class="input">
                            <option value="admin">Admin — full organisation management</option>
                            <option value="analyst">Analyst — create and view analyses</option>
                            <option value="viewer">Viewer — read-only access</option>
                        </select>
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" class="btn btn-outline" @click="$refs.inviteDialog.close()">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="inviting">Send invitation</button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>
@endsection
