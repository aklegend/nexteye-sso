<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nxtey SSO Server URL
    |--------------------------------------------------------------------------
    */
    'server_url' => env('NXTEY_SERVER_URL', 'https://auth.nxtey.com'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Client Credentials
    |--------------------------------------------------------------------------
    */
    'client_id' => env('NXTEY_CLIENT_ID', ''),
    'client_secret' => env('NXTEY_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Redirect URI
    |--------------------------------------------------------------------------
    | Must exactly match the URI registered on the Auth Server.
    */
    'redirect_uri' => env('NXTEY_REDIRECT_URI', 'https://'.request()->getHost().'/sso/callback'),

    /*
    |--------------------------------------------------------------------------
    | Post-Login Redirect
    |--------------------------------------------------------------------------
    */
    'login_redirect_path' => env('NXTEY_LOGIN_REDIRECT_PATH', '/dashboard'),
];