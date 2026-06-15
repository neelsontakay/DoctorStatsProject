@extends('layouts.shell', ['activeNav' => 'Reports'])

@section('title', 'Reports — DoctorStats')

@section('content')
    <div x-data="reportsListPage" x-init="init">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">Reports</h1>
            <p class="text-muted-foreground">Browse and download your analysis reports</p>
        </div>

        <div class="card">
            <div class="card-pad border-b border-border">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-foreground">All Reports</h2>
                    <input type="search" x-model="search" class="input sm:max-w-xs" placeholder="Search reports..." aria-label="Search reports">
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach (['all', 'completed', 'processing', 'pending', 'failed'] as $filter)
                        <button type="button" class="btn btn-sm"
                            :class="filter === '{{ $filter }}' ? 'btn-primary' : 'btn-outline'"
                            @click="filter = '{{ $filter }}'">
                            {{ $filter === 'all' ? 'All' : ucfirst($filter) }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="card-pad">
                <p x-show="error" x-text="error" class="alert-error mb-4"></p>
                <template x-if="loading">
                    <p class="text-muted-foreground">Loading reports...</p>
                </template>
                <template x-if="!loading && filteredReports().length === 0">
                    <div class="text-center py-12">
                        <p class="font-medium text-foreground mb-2">No reports yet</p>
                        <p class="text-sm text-muted-foreground mb-4">Complete your first analysis to generate a report.</p>
                        <a href="{{ route('analyses.create') }}" class="btn btn-primary">New Analysis</a>
                    </div>
                </template>
                <div class="space-y-3">
                    <template x-for="report in filteredReports()" :key="report.id">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg border border-border hover:bg-muted transition-colors">
                            <div class="min-w-0">
                                <p class="font-medium text-foreground truncate" x-text="report.title ?? report.job_id"></p>
                                <p class="text-xs text-muted-foreground">
                                    <span x-text="report.job_id"></span> · <span x-text="formatDate(report.created_at)"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="badge" :class="statusClass(report.status)" x-text="report.status"></span>
                                <a :href="`/reports/${report.id}`" class="btn btn-ghost btn-sm">View</a>
                                <a :href="`/api/v1/reports/${report.id}/download/html`" class="btn btn-ghost btn-sm"
                                    :class="report.status !== 'completed' ? 'opacity-50 pointer-events-none' : ''"
                                    target="_blank" rel="noopener">HTML</a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection
