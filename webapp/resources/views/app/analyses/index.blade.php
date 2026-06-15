@extends('layouts.shell', ['activeNav' => 'Analyses'])

@section('title', 'Analyses — DoctorStats')

@section('content')
    <div x-data="analysesListPage" x-init="init">
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-foreground mb-2">Analyses</h1>
                <p class="text-muted-foreground">Track and manage your statistical analysis jobs</p>
            </div>
            <a href="{{ route('analyses.create') }}" class="btn btn-primary">New Analysis</a>
        </div>

        <div class="card">
            <div class="card-pad border-b border-border">
                <div class="flex flex-wrap gap-2" role="group" aria-label="Filter by status">
                    @foreach (['all', 'completed', 'processing', 'pending', 'failed'] as $filter)
                        <button type="button" class="btn btn-sm"
                            :class="filter === '{{ $filter }}' ? 'btn-primary' : 'btn-outline'"
                            @click="setFilter('{{ $filter }}')">
                            {{ $filter === 'all' ? 'All' : ucfirst($filter) }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="card-pad">
                <p x-show="error" x-text="error" class="alert-error mb-4"></p>
                <template x-if="loading">
                    <p class="text-muted-foreground">Loading analyses...</p>
                </template>
                <template x-if="!loading && jobs.length === 0">
                    <div class="text-center py-12">
                        <p class="font-medium text-foreground mb-2">No analyses found</p>
                        <a href="{{ route('analyses.create') }}" class="btn btn-primary mt-4">Start your first analysis</a>
                    </div>
                </template>
                <div class="space-y-3">
                    <template x-for="job in jobs" :key="job.id">
                        <a :href="`/analyses/${job.job_id}`" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg border border-border hover:bg-muted transition-colors">
                            <div class="min-w-0">
                                <p class="font-medium text-foreground" x-text="job.job_id"></p>
                                <p class="text-sm text-muted-foreground line-clamp-2" x-text="job.objectives"></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-muted-foreground" x-text="formatDate(job.created_at)"></span>
                                <span class="badge" :class="statusClass(job.status)" x-text="job.status"></span>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection
