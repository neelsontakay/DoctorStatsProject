<?php

return [

    'max_upload_bytes' => (int) env('DOCTORSTATS_MAX_UPLOAD_BYTES', 52_428_800), // 50 MB

    'preview_row_limit' => 10,

    'payment_stub_enabled' => (bool) env('DOCTORSTATS_PAYMENT_STUB', true),

    'pay_per_job_amount' => (float) env('DOCTORSTATS_PAY_PER_JOB_AMOUNT', 999.00),

    'pricing' => [
        'currency' => env('DOCTORSTATS_CURRENCY', 'INR'),
        'size_surcharge_per_mb' => (float) env('DOCTORSTATS_SIZE_SURCHARGE_PER_MB', 50),
    ],

    'subscription_plans' => [
        'basic' => [
            'tier' => 'basic',
            'name' => 'Basic',
            'monthly_amount' => 2999,
            'annual_amount' => 29990,
            'analysis_limit' => 10,
            'account_types' => ['individual'],
        ],
        'professional' => [
            'tier' => 'professional',
            'name' => 'Professional',
            'monthly_amount' => 7999,
            'annual_amount' => 79990,
            'analysis_limit' => null,
            'account_types' => ['individual'],
        ],
        'org_basic' => [
            'tier' => 'org_basic',
            'name' => 'Organization Basic',
            'monthly_amount' => 9999,
            'annual_amount' => 99990,
            'analysis_limit' => 10,
            'account_types' => ['organizational'],
        ],
        'org_professional' => [
            'tier' => 'org_professional',
            'name' => 'Organization Professional',
            'monthly_amount' => 19999,
            'annual_amount' => 199990,
            'analysis_limit' => null,
            'account_types' => ['organizational'],
        ],
    ],

    'ai_stub_enabled' => (bool) env('DOCTORSTATS_AI_STUB', true),

    'share_expiry_days' => (int) env('DOCTORSTATS_SHARE_EXPIRY_DAYS', 7),

    'subscription_analysis_limits' => [
        'basic' => 10,
        'org_basic' => 10,
        'professional' => null,
        'org_professional' => null,
        'enterprise' => null,
        'org_enterprise' => null,
    ],

];
