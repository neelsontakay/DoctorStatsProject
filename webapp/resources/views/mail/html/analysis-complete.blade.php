@extends('mail.layouts.doctorstats')

@section('content')
    <h2 style="margin:0 0 16px;font-size:20px;color:#0f172a;">Your analysis is ready</h2>
    <p style="margin:0 0 16px;line-height:1.6;">
        Analysis <strong>{{ $job->job_id }}</strong> has completed successfully.
    </p>
    <p style="margin:0 0 24px;line-height:1.6;">
        Your report <strong>{{ $report->title }}</strong> is now available to view, download, and share.
    </p>
    <a href="{{ config('app.url') }}/reports/{{ $report->id }}"
       style="display:inline-block;background:#0f766e;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold;">
        Open report
    </a>
@endsection
