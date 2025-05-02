<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */
    'default' => env('LOG_CHANNEL', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, framework uses a 'file' driver but you may add others.
    | Available Drivers: "file", "syslog", "errorlog", "null"
    |
    */
    'channels' => [
        'file' => [
            'driver' => 'file',
            'path' => BASE_PATH . '/storage/logs/app.log', // Ensure storage/logs is writable
            'level' => env('LOG_LEVEL', 'debug'), // Minimum level to log
        ],

        'daily' => [
            'driver' => 'file',
            'path' => BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log',
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7, // Optional: Number of days to keep logs
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
