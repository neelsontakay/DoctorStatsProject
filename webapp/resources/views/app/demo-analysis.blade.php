@extends('layouts.shell', ['activeNav' => 'Analyses'])

@section('title', 'Demo analysis — DoctorStats')

@section('content')
    <div x-data="demoAnalysisPage">
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-foreground mb-2">Demo clinical analysis</h1>
                <p class="text-muted-foreground">Hypertension trial dataset with one-click upload and analysis</p>
            </div>
            <a href="{{ url('/demo/clinical-trial-demo.csv') }}" download class="btn btn-outline">Download CSV</a>
        </div>

        <div class="card card-pad">
            <h2 class="text-lg font-semibold text-foreground">Hypertension trial dataset</h2>
            <p class="mt-2 text-sm text-muted-foreground">
                50 synthetic patients in Treatment vs Control arms. Compares
                <strong>post-treatment systolic blood pressure</strong> by group.
            </p>

            <dl class="mt-4 grid gap-2 text-sm text-muted-foreground sm:grid-cols-2">
                <div><dt class="font-medium text-foreground">Rows</dt><dd>50 patients</dd></div>
                <div><dt class="font-medium text-foreground">Format</dt><dd>CSV</dd></div>
                <div><dt class="font-medium text-foreground">Dependent variable</dt><dd>post_treatment_systolic_bp</dd></div>
                <div><dt class="font-medium text-foreground">Primary grouping</dt><dd>treatment_group</dd></div>
            </dl>

            <button type="button" @click="runDemo" :disabled="loading || polling" class="btn btn-primary mt-6">
                <span x-show="!loading && !polling">Run demo analysis</span>
                <span x-show="loading">Uploading and submitting...</span>
                <span x-show="polling && !loading">Processing analysis...</span>
            </button>

            <p x-show="error" x-text="error" class="alert-error mt-4"></p>
        </div>

        <div x-show="job" class="card card-pad mt-6">
            <h2 class="text-lg font-semibold text-foreground">Job status</h2>
            <p class="mt-2 text-sm text-muted-foreground">
                Job ID: <span class="font-mono font-medium" x-text="job?.job_id"></span>
            </p>
            <p class="mt-2 text-sm">
                Status: <span class="badge" :class="statusClass(job?.status)" x-text="job?.status"></span>
            </p>

            <div x-show="reportId" class="mt-4 flex gap-3">
                <a :href="`/reports/${reportId}`" class="btn btn-primary">View report</a>
                <a :href="`/analyses/${job?.job_id}`" class="btn btn-outline">Job details</a>
            </div>

            <p x-show="job?.status === 'pending' && (polling || loading)" class="alert-info mt-4">
                Waiting for analysis to start… Ensure the <strong>Queue worker</strong> and <strong>Stats service</strong> windows from <code>start-native-dev.ps1</code> are open.
            </p>
            <p x-show="job?.status === 'pending' && !polling && !loading" class="alert-error mt-4">
                Job is stuck on pending. Close all terminals, run <code>.\scripts\start-native-dev.ps1</code> from the project root, then click Run demo analysis again.
            </p>
            <p x-show="job?.status === 'processing'" class="alert-info mt-4">
                Analysis in progress… this page updates automatically.
            </p>
            <p x-show="job?.status === 'completed' && !reportId && polling" class="alert-info mt-4">
                Analysis complete — generating report. This can take a minute while graphs are built.
            </p>
            <p x-show="job?.status === 'failed'" class="alert-error mt-4">
                Analysis failed. Restart with <code>.\scripts\start-native-dev.ps1</code> and try again.
            </p>
        </div>
    </div>
@endsection
