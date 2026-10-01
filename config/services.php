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

    // Microsoft / Azure AD — shared by two independent things, both reading
    // through config() rather than a bare env() call: once config is cached
    // (`php artisan config:cache`, standard on production), Laravel stops
    // loading .env on each request, and any env() call outside config/*.php
    // silently returns null (this bit the Chrome extension's audience check
    // in production once already — see CHANGELOG 1.0.0.27).
    //   - client_id: the Azure AD Application (client) ID. Used both by the
    //     Chrome extension's audience check (every login token's "aud" claim
    //     must match it — LoginVerifyController, VerifyMicrosoftToken) and
    //     by the web "Sign in with Microsoft" flow below. Deliberately the
    //     SAME App Registration for both, so there's only one app to manage
    //     in Azure — the web flow just needs its own redirect URI added
    //     there too, which doesn't change the client_id the extension relies on.
    //   - client_secret / redirect: only used by the web Socialite flow
    //     (LoginController::redirectToMicrosoft/handleMicrosoftCallback). The
    //     extension's token flow never needed these — it only ever verifies
    //     a token it already has, it never exchanges a code for one.
    //   - tenant: the Azure AD Directory (tenant) ID. The socialiteproviders
    //     package defaults to the /common endpoint when this isn't set,
    //     which Azure rejects outright (AADSTS50194) for single-tenant App
    //     Registrations — which this one is, and should stay, since this is
    //     an internal company app. Required for the web flow; the
    //     extension's audience check doesn't use it.
    //   - client_id is normalized to the bare Application (client) ID GUID
    //     here, stripping an "api://" prefix if MICROSOFT_CLIENT_ID in .env
    //     has one. Both environments' .env historically stored it WITH the
    //     prefix (that's the Application ID URI format, and it's what the
    //     Chrome extension's access tokens carry as their own 'aud' claim —
    //     see VerifyMicrosoftToken/LoginVerifyController, which reconstruct
    //     that prefix explicitly when comparing). Socialite's OAuth
    //     client_id and the web flow's id_token 'aud' claim need the BARE
    //     GUID instead (OAuth/OIDC convention) — normalizing here means
    //     this works for both consumers no matter which form is sitting in
    //     a given environment's .env, so no .env change was needed to ship this.
    'microsoft' => [
        'client_id' => preg_replace('#^api://#i', '', (string) env('MICROSOFT_CLIENT_ID')),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant' => env('MICROSOFT_TENANT_ID'),
    ],

    'jira' => [
        'url' => env('JIRA_URL'),
        'email' => env('JIRA_EMAIL'),
        'token' => env('JIRA_API_TOKEN'),
    ],

];
