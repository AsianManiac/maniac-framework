<?php
// bootstrap/app.php

/**
 * Bootstrap the Maniac Framework application.
 *
 * This file initializes the application container, loads environment variables,
 * sets up configuration, logging, and binds core services. It returns the
 * application instance for use in the entry point (public/index.php).
 */

use Core\Logging\Log;
use Core\Foundation\App;
use Core\Logging\LogManager;
use Core\Foundation\Facade;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
if (class_exists(\Dotenv\Dotenv::class)) {
    try {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    } catch (\Throwable $e) {
        error_log('Failed to load .env file: ' . $e->getMessage());
    }
}

// Initialize the application container
$app = new App();

// Set the facade application
Facade::setFacadeApplication($app);

// Load configuration
$config = require __DIR__ . '/../config/app.php';
App::bind('config', $config);

// Initialize logging
$logManager = new LogManager($config['logging'] ?? []);
Log::setLogManager($logManager);

// Bind core services
require __DIR__ . '/services.php';

// Bind the App instance itself for container access
App::bind('app', $app);

// Return the application instance
return $app;
