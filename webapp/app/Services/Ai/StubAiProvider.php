<?php

namespace App\Services\Ai;

use App\Contracts\Ai\AiProvider;

class StubAiProvider implements AiProvider
{
    public function name(): string
    {
        return 'stub';
    }

    public function interpret(array $context): array
    {
        $objectives = (string) ($context['objectives'] ?? 'the stated research objectives');
        $testCount = count($context['tests'] ?? []);
        $rowCount = $context['data_profile']['row_count'] ?? 'unknown';

        return [
            'executive_summary' => "This automated report summarizes {$testCount} statistical test(s) performed on a dataset of {$rowCount} rows, aligned with {$objectives}. Review the detailed results and visualizations below before making clinical or research decisions.",
            'interpretation' => 'The statistical engine identified measurable patterns in the uploaded dataset. Significant findings should be interpreted alongside study design, sampling strategy, and clinical context. Non-significant results may reflect limited sample size or high variability rather than the absence of an effect.',
            'limitations' => 'Automated test selection cannot replace domain expertise. Missing data handling, confounding, and assumption violations may affect validity. External validation is recommended before operational or clinical application.',
            'recommendations' => 'Consider expanding the sample size, validating key associations in an independent cohort, and consulting a biostatistician for confirmatory analysis planning.',
        ];
    }
}
