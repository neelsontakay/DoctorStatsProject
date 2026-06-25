<?php

namespace App\Services;

use App\Models\AnalysisJob;
use Illuminate\Support\Facades\View;

class ReportAssemblyService
{
    /**
     * @param  array{
     *     executive_summary: string,
     *     interpretation: string,
     *     limitations: string,
     *     recommendations: string,
     *     provider: string,
     * }  $aiContent
     * @param  list<array{graph_type: string, title: string, caption: string, storage_path?: string, image_base64?: string, mime_type?: string}>  $graphs
     */
    public function renderHtml(AnalysisJob $job, array $aiContent, array $graphs): string
    {
        $job->loadMissing(['columns', 'results', 'dataFile', 'user']);

        $profile = $job->results
            ->firstWhere('test_category', 'profile')
            ?->raw_output ?? [];

        $tests = $job->results
            ->where('test_category', '!=', 'profile')
            ->values();

        $frequencyTests = $tests->where('test_category', 'frequency')->values();
        $hypothesisTests = $tests->where('test_category', 'hypothesis')->values();
        $otherTests = $tests
            ->whereNotIn('test_category', ['frequency', 'hypothesis'])
            ->values();

        return View::make('reports.analysis-report', [
            'job' => $job,
            'profile' => $profile,
            'tests' => $tests,
            'frequencyTests' => $frequencyTests,
            'hypothesisTests' => $hypothesisTests,
            'otherTests' => $otherTests,
            'graphs' => $graphs,
            'ai' => $aiContent,
            'generatedAt' => now(),
        ])->render();
    }
}
