<?php
// bootstrap/services.php

/**
 * Bind core services to the Maniac Framework application container.
 */

use Core\Http\Request;
use Core\Foundation\App;
use Core\View\NiacEngine;
use Core\Encryption\Encrypter;
use Core\View\ViewEngineInterface;
use Core\Http\Response\ResponseFactory;
use Core\Http\Response\ResponseFactoryInterface;
use Core\Database\DB;
use Core\Console\Application;

// Define the base path of your application (root directory)
$basePath = dirname(__DIR__);

/**
 * Bind Console Application.
 */
App::bind('console', function () {
    return new Application();
});

/**
 * Bind Database.
 */
App::bind('db', function () use ($basePath) {
    $config = require $basePath . '/config/database.php';
    DB::init($config);
    return DB::getInstance();
});

/**
 * Bind Request service.
 */
App::bind(Request::class, function () {
    return Request::createFromGlobals();
});

/**
 * Bind ViewEngineInterface to NiacEngine.
 */
App::bind(ViewEngineInterface::class, function () use ($basePath) {
    return new NiacEngine(
        $basePath . '/resources/views',
        $basePath . '/storage/framework/views'
    );
});

/**
 * Bind ResponseFactory.
 */
App::bind(ResponseFactoryInterface::class, function () {
    return new ResponseFactory(app(ViewEngineInterface::class));
});

/**
 * Bind Encrypter.
 */
App::bind(Encrypter::class, function () {
    $key = env('APP_KEY');
    if (empty($key)) {
        throw new RuntimeException('No APP_KEY set. Run `php maniac key:gen` to generate one.');
    }
    if (strpos($key, 'base64:') === 0) {
        $key = base64_decode(substr($key, 7));
        if ($key === false) {
            throw new RuntimeException('Invalid APP_KEY: Base64 decoding failed.');
        }
    }
    $cipher = env('APP_CIPHER', 'AES-256-GCM');
    $keyLength = mb_strlen($key, '8bit');
    if (($cipher === 'AES-128-GCM' && $keyLength !== 16) || ($cipher === 'AES-256-GCM' && $keyLength !== 32)) {
        throw new RuntimeException("Invalid APP_KEY length. Must be 16 bytes for AES-128-GCM or 32 bytes for AES-256-GCM. Current length: {$keyLength} bytes.");
    }
    return new Encrypter($key);
});
