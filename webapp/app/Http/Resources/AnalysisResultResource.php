<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AnalysisResult */
class AnalysisResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_name' => $this->test_name,
            'test_category' => $this->test_category,
            'parameters' => $this->parameters,
            'test_statistic' => $this->test_statistic,
            'p_value' => $this->p_value,
            'confidence_intervals' => $this->confidence_intervals,
            'effect_sizes' => $this->effect_sizes,
            'assumptions_validation' => $this->assumptions_validation,
            'raw_output' => $this->raw_output,
        ];
    }
}
