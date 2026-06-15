<?php

namespace App\Services;

use App\Contracts\Ai\AiProvider;
use App\Models\AnalysisJob;
use App\Services\Ai\AnthropicAiProvider;
use App\Services\Ai\GeminiAiProvider;
use App\Services\Ai\StubAiProvider;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiInterpretationService
{
    public function __construct(
        private readonly StubAiProvider $stubProvider,
        private readonly AnthropicAiProvider $anthropicProvider,
        private readonly GeminiAiProvider $geminiProvider,
    ) {}

    /**
     * @return array{
     *     executive_summary: string,
     *     interpretation: string,
     *     limitations: string,
     *     recommendations: string,
     *     provider: string,
     * }
     */
    public function interpret(AnalysisJob $job): array
    {
        $context = $this->buildContext($job);

        foreach ($this->providers() as $provider) {
            try {
                $result = $provider->interpret($context);

                return [
                    ...$result,
                    'provider' => $provider->name(),
                ];
            } catch (Throwable $exception) {
                Log::warning('AI provider failed, trying fallback.', [
                    'provider' => $provider->name(),
                    'job_id' => $job->job_id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $result = $this->stubProvider->interpret($context);

        return [
            ...$result,
            'provider' => $this->stubProvider->name(),
        ];
    }

    /**
     * @return list<AiProvider>
     */
    private function providers(): array
    {
        if (config('doctorstats.ai_stub_enabled')) {
            return [$this->stubProvider];
        }

        return [$this->anthropicProvider, $this->geminiProvider, $this->stubProvider];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(AnalysisJob $job): array
    {
        $job->loadMissing(['columns', 'results', 'dataFile']);

        $profile = $job->results
            ->firstWhere('test_category', 'profile')
            ?->raw_output ?? [];

        $tests = $job->results
            ->where('test_category', '!=', 'profile')
            ->map(fn ($result) => [
                'test_name' => $result->test_name,
                'test_category' => $result->test_category,
                'parameters' => $result->parameters,
                'test_statistic' => $result->test_statistic,
                'p_value' => $result->p_value,
                'effect_sizes' => $result->effect_sizes,
                'assumptions_validation' => $result->assumptions_validation,
            ])
            ->values()
            ->all();

        return [
            'job_id' => $job->job_id,
            'objectives' => $job->objectives,
            'columns' => $job->columns->map(fn ($column) => [
                'name' => $column->column_name,
                'data_type' => $column->data_type->value,
                'description' => $column->description,
                'unit' => $column->unit_of_measurement,
                'variable_type' => $column->variable_type->value,
            ])->values()->all(),
            'data_profile' => $profile,
            'tests' => $tests,
        ];
    }
}
