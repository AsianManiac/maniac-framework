<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    | Default mailer configuration to use for sending emails.
    | References keys in the 'mailers' array below.
    */
    'default' => env('MAIL_MAILER', 'array'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    | Define different mailer setups (transports).
    | Supported: 'smtp', 'sendmail', 'array', 'failover', 'roundrobin'
    | Use DSN format for transports: https://symfony.com/doc/current/mailer.html#transport-setup
    */
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'dsn' => env('MAIL_DSN'),
            'host' => env('MAIL_HOST', 'smtp.example.com'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'dsn' => 'sendmail://default',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs'),
        ],

        'array' => [
            'transport' => 'in-memory',
            'dsn' => 'in-memory://default',
        ],

        'failover' => [
            'transport' => 'failover',
            'dsn' => 'failover(smtp://user:pass@primary?timeout=10 sendmail://default)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Maniac Framework'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    */
    'markdown' => [
        'theme' => 'modern',
        'paths' => [
            dirname(__DIR__) . '/resources/views/vendor/mail/html',
        ],
    ],
];
