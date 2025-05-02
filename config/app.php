<?php
// config/app.php

/**
 * Application configuration for the Maniac Framework.
 *
 * Defines core configuration settings for the application, including URLs,
 * encryption, debugging, view paths, and logging. Values are loaded from
 * environment variables using the env() helper.
 *
 * @return array Configuration settings.
 */

// Ensure view and cache directories exist
$viewsDir = __DIR__ . '/../resources/views';
$cacheDir = __DIR__ . '/../storage/framework/views';
if (!is_dir($viewsDir)) {
    mkdir($viewsDir, 0775, true);
}
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0775, true);
}

return [
    /**
     * Base URL of the application.
     */
    'url' => env('APP_URL', 'http://localhost:8000'),

    /**
     * Application encryption key for secure operations.
     */
    'key' => env('APP_KEY'),

    /**
     * Encryption cipher (e.g., AES-256-GCM or AES-128-GCM).
     */
    'cipher' => env('APP_CIPHER', 'AES-256-GCM'),

    /**
     * Enable debug mode for detailed error reporting.
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * Base URL for assets (defaults to app.url if not specified).
     */
    'asset_url' => env('ASSET_URL', env('APP_URL', 'http://localhost:8000')),

    /**
     * View configuration.
     */
    'view' => [
        'paths' => realpath($viewsDir) ?: $viewsDir,
        'compiled' => realpath($cacheDir) ?: $cacheDir,
    ],

    /**
     * Alias for view paths (for compatibility with helpers).
     */
    'views_path' => realpath($viewsDir) ?: $viewsDir,

    /**
     * Alias for compiled views path (for compatibility with helpers).
     */
    'cache_path' => realpath($cacheDir) ?: $cacheDir,

    /**
     * Logging configuration.
     */
    'logging' => [
        'default' => 'file',
        'channels' => [
            'file' => [
                'driver' => 'file',
                'path' => realpath(__DIR__ . '/../storage/logs/app.log') ?: __DIR__ . '/../storage/logs/app.log',
            ],
        ],
    ],

    /**
     * Path configurations for assets and public directory.
     */
    'css_path' => realpath(__DIR__ . '/../resources/css') ?: __DIR__ . '/../resources/css',
    'js_path' => realpath(__DIR__ . '/../resources/js') ?: __DIR__ . '/../resources/js',
    'public_path' => realpath(__DIR__ . '/../public') ?: __DIR__ . '/../public',
];
