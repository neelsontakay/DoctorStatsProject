You have been invited to join {{ $invitation->organization->name }} on DoctorStats.

Role: {{ $invitation->role->value }}

Accept your invitation by signing in and visiting:
{{ config('app.url') }}/invitations/{{ $invitation->token }}/accept

This invitation expires on {{ $invitation->expires_at->toDayDateTimeString() }}.
