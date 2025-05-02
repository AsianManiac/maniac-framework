<?php

use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    | Supported: "file", "database", "cookie", "array", "null"
    */
    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    | Lifetime in minutes. Expired sessions are cleaned up automatically.
    */
    'lifetime' => env('SESSION_LIFETIME', 120), // 2 hours

    /*
    |--------------------------------------------------------------------------
    | Session File Location (for 'file' driver)
    |--------------------------------------------------------------------------
    */
    'files' => env('SESSION_FILES', BASE_PATH . '/storage/framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    */
    'cookie' => env('SESSION_COOKIE', 'maniac_session'),

    /*
    |--------------------------------------------------------------------------
    | Default Cookie Path
    |--------------------------------------------------------------------------
    */
    'path' => env('COOKIE_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Default Cookie Domain
    |--------------------------------------------------------------------------
    */
    'domain' => env('COOKIE_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Default Secure Cookie Setting
    |--------------------------------------------------------------------------
    | Send cookies only over HTTPS. Set to true in production.
    */
    'secure' => env('COOKIE_SECURE', false),

    /*
    |--------------------------------------------------------------------------
    | Default HTTP Access Only Setting
    |--------------------------------------------------------------------------
    | Prevent JavaScript access to the cookie.
    */
    'http_only' => env('COOKIE_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Default SameSite Cookie Option
    |--------------------------------------------------------------------------
    | Options: 'lax', 'strict', 'none', null. 'none' requires 'secure' => true.
    */
    'same_site' => env('COOKIE_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Encrypt Cookies
    |--------------------------------------------------------------------------
    | Automatically encrypt/decrypt cookie values using APP_KEY.
    | Requires Encrypter service. Set to false if not using encryption.
    */
    'encrypt' => env('COOKIE_ENCRYPT', true), // Default to true for security
];
