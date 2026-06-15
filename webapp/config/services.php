<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stats_service' => [
        'url' => env('STATS_SERVICE_URL', 'http://localhost:8001'),
        'token' => env('STATS_SERVICE_TOKEN'),
        'timeout' => (int) env('STATS_SERVICE_TIMEOUT', 900),
        'require_token' => (bool) env('STATS_SERVICE_REQUIRE_TOKEN', false),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-pro'),
    ],

    'zoho_pay' => [
        'api_key' => env('ZOHO_PAY_API_KEY'),
        'account_id' => env('ZOHO_PAY_ACCOUNT_ID'),
        'webhook_secret' => env('ZOHO_PAY_WEBHOOK_SECRET'),
        'base_url' => env('ZOHO_PAY_BASE_URL', 'https://payments.zoho.com/api/v1'),
    ],

    'zeptomail' => [
        'api_key' => env('ZEPTOMAIL_API_KEY'),
        'api_url' => env('ZEPTOMAIL_API_URL', 'https://api.zeptomail.com/v1.1'),
        'bounce_address' => env('ZEPTOMAIL_BOUNCE_ADDRESS'),
    ],

];
