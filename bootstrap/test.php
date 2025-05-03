<?php

/**
 * Bootstrap the Maniac Framework for test scripts.
 *
 * Initializes the application container, environment, and core services.
 */

use Core\Logging\Log;
use Core\Foundation\App;
use Core\Logging\LogManager;
use Core\Foundation\Facade;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
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

// Bind the App instance
App::bind('app', $app);

// Initialize database
use Core\Database\DB;

DB::init(require __DIR__ . '/../config/database.php');

// Return the application instance
return $app;
