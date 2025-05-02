<?php

/**
 * Web Routes for the Maniac Framework.
 *
 * Defines HTTP routes for the application, mapping URIs to controllers or
 * closures. Supports grouping, middleware, and various HTTP methods.
 */

use Core\Http\Request;
use Core\Routing\Router;
use App\Middleware\AuthMiddleware;
use App\Controllers\HomeController;
use App\Controllers\UserController;

/** @var Router $router */
$router->get('/', function () {
    return view('welcome', ['message' => 'Welcome to Your Framework!']);
});

$router->get('/hello/{name}', function (string $name, Request $request) {
    return "Hello, " . htmlspecialchars($name);
});

// $router->get('/home', [HomeController::class, 'index']);
$router->get('/users', [HomeController::class, 'index']);

// $router->group(['prefix' => 'admin', 'middleware' => AuthMiddleware::class], function (Router $router) {
//     $router->get('/', function () {
//         return 'Admin Dashboard';
//     });

//     $router->get('/users', [UserController::class, 'index'])->middleware('AnotherMiddleware');
//     $router->get('/users/create', [UserController::class, 'create']);
//     $router->post('/users', [UserController::class, 'store']);
//     $router->get('/users/{id}', [UserController::class, 'show']);
//     $router->put('/users/{id}', [UserController::class, 'update']);
//     $router->delete('/users/{id}', [UserController::class, 'destroy']);
// });

$router->post('/submit', function (Request $request) {
    return ['status' => 'success', 'data' => $request->all()];
});

$router->put('/update/{id}', function (int $id, Request $request) {
    return "Updating resource with ID: {$id}";
});

// routes/web.php
$router->get('/test-encrypt', function () {
    $encrypter = app(\Core\Encryption\Encrypter::class);
    $encrypted = $encrypter->encrypt('Test data');
    $decrypted = $encrypter->decrypt($encrypted);
    return "Encrypted: {$encrypted}<br>Decrypted: {$decrypted}";
});
