@extends('mail.layouts.doctorstats')

@section('content')
    <h2 style="margin:0 0 16px;font-size:20px;color:#0f172a;">You're invited to join {{ $invitation->organization->name }}</h2>
    <p style="margin:0 0 16px;line-height:1.6;">
        You have been invited to collaborate on DoctorStats as a <strong>{{ $invitation->role->value }}</strong>.
    </p>
    <p style="margin:0 0 24px;line-height:1.6;">
        Accept your invitation to access shared analyses, reports, and organization analytics.
    </p>
    <a href="{{ config('app.url') }}/invitations/{{ $invitation->token }}/accept"
       style="display:inline-block;background:#0f766e;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold;">
        Accept invitation
    </a>
    <p style="margin:24px 0 0;font-size:13px;color:#64748b;line-height:1.6;">
        This invitation expires on {{ $invitation->expires_at->toDayDateTimeString() }}.
    </p>
@endsection
