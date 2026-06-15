{{ $share->sharer->name ?? 'A colleague' }} shared a DoctorStats report with you.

Report: {{ $share->report->title }}

View the report:
{{ config('app.url') }}/shared/{{ $share->token }}

@if ($share->expires_at)
This link expires on {{ $share->expires_at->toDayDateTimeString() }}.
@endif
