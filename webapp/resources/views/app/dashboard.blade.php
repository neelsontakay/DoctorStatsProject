@extends('layouts.shell', ['activeNav' => 'Dashboard'])

@section('title', 'Dashboard — DoctorStats')

@section('content')
    <div x-data="dashboardPage" x-init="init">
        <template x-if="error">
            <div class="alert-error mb-6">
                <p x-text="error"></p>
                <button type="button" class="btn btn-outline btn-sm mt-3" @click="load">Retry</button>
            </div>
        </template>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2" x-text="`Welcome back, ${firstName()}`"></h1>
            <p class="text-muted-foreground">Here's what's happening with your analyses today</p>
        </div>

        <template x-if="loading">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                @foreach (range(1, 4) as $i)
                    <div class="card card-pad animate-pulse">
                        <div class="h-4 bg-muted rounded w-2/3 mb-4"></div>
                        <div class="h-8 bg-muted rounded w-1/2"></div>
                    </div>
                @endforeach
            </div>
        </template>

        <template x-if="!loading && !error">
            <div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="card card-pad">
                        <p class="text-sm font-medium text-muted-foreground mb-4">Total Analyses</p>
                        <p class="text-3xl font-bold text-foreground" x-text="dashboard?.total_analyses ?? 0"></p>
                    </div>
                    <div class="card card-pad">
                        <p class="text-sm font-medium text-muted-foreground mb-4">Completed</p>
                        <p class="text-3xl font-bold text-accent" x-text="dashboard?.jobs_by_status?.completed ?? 0"></p>
                    </div>
                    <div class="card card-pad">
                        <p class="text-sm font-medium text-muted-foreground mb-4">In Progress</p>
                        <p class="text-3xl font-bold text-secondary" x-text="(dashboard?.jobs_by_status?.processing ?? 0) + (dashboard?.jobs_by_status?.pending ?? 0)"></p>
                    </div>
                    <div class="card card-pad">
                        <p class="text-sm font-medium text-muted-foreground mb-4">Subscription</p>
                        <p class="text-xl font-bold text-foreground capitalize" x-text="dashboard?.active_subscription?.plan_tier ?? 'None'"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 card">
                        <div class="card-pad border-b border-border flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-foreground">Recent Analyses</h2>
                            <a href="{{ route('analyses.index') }}" class="btn btn-outline btn-sm">View All</a>
                        </div>
                        <div class="card-pad">
                            <template x-if="(dashboard?.recent_jobs ?? []).length === 0">
                                <div class="text-center py-8">
                                    <p class="font-medium text-foreground mb-2">No analyses yet</p>
                                    <p class="text-sm text-muted-foreground mb-4">Upload your first dataset to get AI-powered statistical insights.</p>
                                    <a href="{{ route('analyses.create') }}" class="btn btn-primary">New Analysis</a>
                                </div>
                            </template>
                            <div class="space-y-4">
                                <template x-for="job in dashboard?.recent_jobs ?? []" :key="job.id">
                                    <a :href="`/analyses/${job.job_id}`" class="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted transition-colors">
                                        <div class="min-w-0">
                                            <p class="font-medium text-foreground truncate" x-text="job.job_id"></p>
                                            <p class="text-sm text-muted-foreground truncate" x-text="job.objectives"></p>
                                        </div>
                                        <span class="badge" :class="statusClass(job.status)" x-text="job.status"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="card card-pad">
                            <h2 class="text-lg font-semibold text-foreground mb-4">Quick actions</h2>
                            <div class="space-y-3">
                                <a href="{{ route('analyses.create') }}" class="btn btn-primary w-full">New Analysis</a>
                                <a href="{{ route('analysis.demo') }}" class="btn btn-outline w-full">Run Demo Analysis</a>
                                <a href="{{ route('reports.index') }}" class="btn btn-outline w-full">Browse Reports</a>
                            </div>
                        </div>
                        <template x-if="dashboard?.organization">
                            <div class="card card-pad">
                                <h2 class="text-lg font-semibold text-foreground mb-2" x-text="dashboard.organization.name"></h2>
                                <p class="text-sm text-muted-foreground">
                                    <span x-text="dashboard.organization.member_count"></span> members ·
                                    <span x-text="dashboard.organization.shared_analyses_count"></span> shared analyses
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection
