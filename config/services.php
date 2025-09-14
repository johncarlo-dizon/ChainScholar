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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'google' => [
        'api_key' => env('GOOGLE_API_KEY'),
        'cse_id' => env('GOOGLE_CSE_ID'),
    ],
  
    // ...
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 18),
    ],

    'copyleaks' => [
        'email'          => env('COPYLEAKS_EMAIL'),
        'key'            => env('COPYLEAKS_API_KEY'),
        'webhook'        => env('COPYLEAKS_WEBHOOK_URL'),
        'signing_secret' => env('COPYLEAKS_WEBHOOK_SECRET'),
        'sandbox'        => (bool) env('COPYLEAKS_SANDBOX', true), // ← toggle here
        'export_base'  => env('COPYLEAKS_EXPORT_BASE', null),
         'min_percent'    => env('COPYLEAKS_MIN_PERCENT', 5), 
    ],





 


];
