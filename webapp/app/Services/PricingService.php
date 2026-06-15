<?php

namespace App\Services;

use App\Models\DataFile;

class PricingService
{
    /**
     * @return array{amount: float, currency: string, breakdown: array<string, float|int>}
     */
    public function quoteForAnalysis(?DataFile $dataFile = null): array
    {
        $base = (float) config('doctorstats.pay_per_job_amount');
        $sizeMb = $dataFile !== null
            ? round($dataFile->file_size_bytes / 1_048_576, 2)
            : 0;

        $sizeSurcharge = max(0, $sizeMb - 10) * (float) config('doctorstats.pricing.size_surcharge_per_mb', 50);

        $amount = round($base + $sizeSurcharge, 2);

        return [
            'amount' => $amount,
            'currency' => (string) config('doctorstats.pricing.currency', 'INR'),
            'breakdown' => [
                'base_amount' => $base,
                'file_size_mb' => $sizeMb,
                'size_surcharge' => round($sizeSurcharge, 2),
            ],
        ];
    }
}
