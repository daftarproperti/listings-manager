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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'telegram' => [
        'webhook_access_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    ],

    'chatgpt' => [
        'key' => env('CHATGPT_KEY'),
        'endpoint' => env('CHATGPT_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'model_version' => env('CHATGPT_MODEL_VERSION', 'gpt-3.5-turbo'),
    ],

    'msg_classification' => [
        'enabled' => env('MSG_CLASSIFICATION_ENABLED', false),
        'endpoint' => env('MSG_CLASSIFICATION_ENDPOINT'),
    ],

    'google' => [
        'project_id' => env('GOOGLE_PROJECT_ID'),
        'bucket_name' => env('GOOGLE_BUCKET_NAME'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        // Socialite can resolve the relative URL to absolute:
        // https://github.com/laravel/socialite/blob/v5.12.1/src/SocialiteManager.php#L219
        'redirect' => '/admin/login/google/callback',
    ],

    'default_listing_city' => env('DEFAULT_LISTING_CITY', ''),

    'whatsapp' => [
        'secret' => env('WHATSAPP_SECRET'),
        'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com/v19.0/'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],
];
