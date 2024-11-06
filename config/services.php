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

    'chatgpt' => [
        'key' => env('CHATGPT_KEY'),
        'endpoint' => env('CHATGPT_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'model_version' => env('CHATGPT_MODEL_VERSION', 'gpt-3.5-turbo'),
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

    'otp_method' => env('OTP_SENDING_METHOD', 'TWILIO'),

    'whatsapp' => [
        'secret' => env('WHATSAPP_SECRET'),
        'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com/v19.0/'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    'twilio' => [
        'base_url' => env('TWILIO_BASE_URL'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'phone_number' => env('TWILIO_PHONE_NUMBER'),
    ],

    'root_users' => explode(',', env('ROOT_USERS', '')),

    'max_listings_per_user' => env('MAX_LISTINGS_PER_USER', null),

    'post_approval_change_users' => explode(',', env('POST_APPROVAL_CHANGE_USERS', '')),
];
