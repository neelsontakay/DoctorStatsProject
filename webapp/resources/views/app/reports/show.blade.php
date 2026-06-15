@extends('layouts.shell', ['activeNav' => 'Reports'])

@section('title', 'Report — DoctorStats')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('reports.index') }}" class="text-sm text-primary hover:underline">&larr; Back to reports</a>
        <div class="flex gap-2">
            <a href="{{ url('/api/v1/reports/'.$reportId.'/download/html') }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Download HTML</a>
            <a href="{{ url('/api/v1/reports/'.$reportId.'/download/pdf') }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Download PDF</a>
        </div>
    </div>

    <div class="card overflow-hidden" style="min-height: 70vh;">
        <iframe
            src="{{ url('/api/v1/reports/'.$reportId.'/view') }}"
            title="Analysis report"
            class="w-full border-0"
            style="min-height: 70vh;"
        ></iframe>
    </div>
@endsection
