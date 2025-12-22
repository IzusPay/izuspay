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
    'witetec' => [
        'key' => env('WITETEC_API_KEY'),
        'url' => env('WITETEC_API_URL'),
        'webhook_secret' => env('WITETEC_WEBHOOK_SECRET'),
    ],
    'brpagg' => [
        'url' => env('BRPAGG_API_URL', 'https://api.brpagg.com.br'),
        'username' => env('BRPAGG_USERNAME'),
        'password' => env('BRPAGG_PASSWORD'),
        'api_key' => env('BRPAGG_API_KEY'),
        'secret_key' => env('BRPAGG_SECRET_KEY'),
        'company_id' => env('BRPAGG_COMPANY_ID'),
    ],

];
