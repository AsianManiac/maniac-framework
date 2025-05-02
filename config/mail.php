<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    | Default mailer configuration to use for sending emails.
    | References keys in the 'mailers' array below.
    */
    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    | Define different mailer setups (transports).
    | Supported: 'smtp', 'sendmail', 'log', 'array', 'failover', 'roundrobin'
    | Use DSN format for transports: https://symfony.com/doc/current/mailer.html#transport-setup
    */
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp', // Indicates DSN usage
            // Example DSN: smtp://user:pass@smtp.example.com:port
            // Or use individual params which will be composed into a DSN
            'dsn' => env('MAIL_DSN'), // Recommended to use DSN
            // --- OR Individual Params (if DSN not provided) ---
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'), // e.g., 'tls', 'ssl', null
            'timeout' => null,
            'auth_mode' => null,
        ],

        'sendmail' => [
            'transport' => 'sendmail', // Use system's sendmail
            'dsn' => 'sendmail://default', // Or specify path: sendmail://%2Fusr%2Fsbin%2Fsendmail%20-t
            // 'command' => '/usr/sbin/sendmail -bs', // Alternative command config
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs'),
        ],

        'log' => [
            'transport' => 'log',
            'dsn' => 'log://default', // Logs to default PSR-3 logger
            'channel' => env('MAIL_LOG_CHANNEL', 'mail'), // Optional: Specify logging channel from config/logging.php
        ],

        'array' => [
            'transport' => 'in-memory', // Symfony's test transport
            'dsn' => 'in-memory://default',
        ],

        'failover' => [
            'transport' => 'failover',
            'dsn' => 'failover(smtp://user:pass@primary?timeout=10 sendmail://default)', // Example DSN
            // 'mailers' => ['smtp', 'sendmail'], // Alternative config using named mailers
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Maniac App'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    */
    'markdown' => [
        'theme' => 'default', // Corresponds to a view in resources/views/vendor/mail/html/themes
        'paths' => [
            realpath(__DIR__ . '/resources/views/vendor/mail'),
        ],
    ],
];
