@extends('layouts.shell', ['activeNav' => 'Analyses'])

@section('title', 'Analysis job — DoctorStats')

@section('content')
    <div x-data="analysisJobPage(@js($jobId))" x-init="init">
        <div class="mb-6">
            <a href="{{ route('analyses.index') }}" class="text-sm text-primary hover:underline">&larr; Back to analyses</a>
        </div>

        <template x-if="loading">
            <p class="text-muted-foreground">Loading job details...</p>
        </template>

        <template x-if="!loading && error">
            <p class="alert-error" x-text="error"></p>
        </template>

        <template x-if="!loading && job">
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl font-bold text-foreground mb-2" x-text="job.job_id"></h1>
                    <p class="text-muted-foreground" x-text="job.objectives"></p>
                </div>

                <div class="card card-pad">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="badge text-sm" :class="statusClass(job.status)" x-text="job.status"></span>
                        <template x-if="polling">
                            <span class="text-sm text-muted-foreground">Updating status...</span>
                        </template>
                    </div>

                    <template x-if="job.status === 'processing' || job.status === 'pending'">
                        <div class="mt-6">
                            <div class="h-2 bg-muted rounded-full overflow-hidden">
                                <div class="h-full bg-primary animate-indeterminate w-1/4 rounded-full"></div>
                            </div>
                            <p class="text-sm text-muted-foreground mt-2">Your analysis is being processed. This page will update automatically.</p>
                        </div>
                    </template>

                    <template x-if="job.status === 'failed'">
                        <p class="alert-error mt-4">Analysis failed. Ensure the queue worker and stats-service are running, then try again.</p>
                    </template>

                    <template x-if="job.status === 'completed' && reportId">
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a :href="`/reports/${reportId}`" class="btn btn-primary">View Report</a>
                            <a :href="`/api/v1/reports/${reportId}/download/html`" class="btn btn-outline" target="_blank" rel="noopener">Download HTML</a>
                        </div>
                    </template>
                </div>

                <div class="card card-pad" x-show="job.columns?.length">
                    <h2 class="text-lg font-semibold text-foreground mb-4">Column mapping</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left text-muted-foreground">
                                    <th class="py-2 pr-4">Column</th>
                                    <th class="py-2 pr-4">Type</th>
                                    <th class="py-2">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="col in job.columns ?? []" :key="col.column_name">
                                    <tr class="border-b border-border">
                                        <td class="py-2 pr-4 font-medium" x-text="col.column_name"></td>
                                        <td class="py-2 pr-4 capitalize" x-text="col.data_type"></td>
                                        <td class="py-2 capitalize" x-text="col.variable_type"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <template x-if="job.status === 'completed' && results.length">
                    <div class="space-y-6">
                        <div class="card card-pad" x-show="frequencyResults().length">
                            <h2 class="text-lg font-semibold text-foreground mb-4">Frequency analysis</h2>
                            <template x-for="result in frequencyResults()" :key="result.id">
                                <div class="mb-4 pb-4 border-b border-border last:border-0 last:pb-0 last:mb-0">
                                    <h3 class="font-medium text-foreground" x-text="result.test_name"></h3>
                                    <template x-if="result.raw_output?.frequency_table?.length">
                                        <div class="overflow-x-auto mt-2">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b border-border text-left text-muted-foreground">
                                                        <th class="py-1 pr-3">Value</th>
                                                        <th class="py-1 pr-3">Count</th>
                                                        <th class="py-1 pr-3">%</th>
                                                        <th class="py-1">Cumulative %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="row in result.raw_output.frequency_table" :key="row.value">
                                                        <tr class="border-b border-border">
                                                            <td class="py-1 pr-3" x-text="row.value"></td>
                                                            <td class="py-1 pr-3" x-text="row.count"></td>
                                                            <td class="py-1 pr-3" x-text="row.percent"></td>
                                                            <td class="py-1" x-text="row.cumulative_percent"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div class="card card-pad" x-show="hypothesisResults().length">
                            <h2 class="text-lg font-semibold text-foreground mb-4">Hypothesis tests</h2>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-border text-left text-muted-foreground">
                                            <th class="py-2 pr-4">Test</th>
                                            <th class="py-2 pr-4">Statistic</th>
                                            <th class="py-2 pr-4">P-value</th>
                                            <th class="py-2">Library</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="result in hypothesisResults()" :key="result.id">
                                            <tr class="border-b border-border">
                                                <td class="py-2 pr-4" x-text="result.test_name"></td>
                                                <td class="py-2 pr-4" x-text="result.test_statistic ?? '—'"></td>
                                                <td class="py-2 pr-4" x-text="formatPValue(result.p_value)"></td>
                                                <td class="py-2 capitalize" x-text="libraryLabel(result.parameters)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
@endsection
