<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AnalysisColumn */
class AnalysisColumnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'column_name' => $this->column_name,
            'column_index' => $this->column_index,
            'data_type' => $this->data_type->value,
            'description' => $this->description,
            'unit_of_measurement' => $this->unit_of_measurement,
            'variable_type' => $this->variable_type->value,
            'quality_warnings' => $this->quality_warnings,
        ];
    }
}
