<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DataFile */
class DataFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_filename' => $this->original_filename,
            'format' => $this->format->value,
            'file_size_bytes' => $this->file_size_bytes,
            'sheet_name' => $this->sheet_name,
            'virus_scan_status' => $this->virus_scan_status->value,
            'preview_stats' => $this->preview_stats,
            'preview_stats_computed_at' => $this->preview_stats_computed_at?->toIso8601String(),
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
