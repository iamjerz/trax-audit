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

    // Chrome extension's Microsoft SSO — the Azure AD Application (client) ID
    // every login token's "aud" claim must match (LoginVerifyController,
    // VerifyMicrosoftToken). Read through config() here rather than a bare
    // env() call in application code: once config is cached
    // (`php artisan config:cache`, standard on production), Laravel stops
    // loading .env on each request, and any env() call outside config/*.php
    // silently returns null — which made the audience check always fail
    // with "Invalid audience" in production while working fine on dev
    // (which never caches config). Config values baked in at cache time
    // aren't affected, so this indirection survives config:cache.
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
    ],

    'jira' => [
        'url' => env('JIRA_URL'),
        'email' => env('JIRA_EMAIL'),
        'token' => env('JIRA_API_TOKEN'),
    ],

];
