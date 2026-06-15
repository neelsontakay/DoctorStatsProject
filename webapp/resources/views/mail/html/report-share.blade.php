@extends('mail.layouts.doctorstats')

@section('content')
    <h2 style="margin:0 0 16px;font-size:20px;color:#0f172a;">A report was shared with you</h2>
    <p style="margin:0 0 16px;line-height:1.6;">
        {{ $share->sharer->name ?? 'A colleague' }} shared the report
        <strong>{{ $share->report->title }}</strong> with you on DoctorStats.
    </p>
    <a href="{{ config('app.url') }}/shared/{{ $share->token }}"
       style="display:inline-block;background:#0f766e;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold;">
        View report
    </a>
    @if ($share->expires_at)
        <p style="margin:24px 0 0;font-size:13px;color:#64748b;line-height:1.6;">
            This secure link expires on {{ $share->expires_at->toDayDateTimeString() }}.
        </p>
    @endif
@endsection
