<?php

use App\Middleware\AuthenticateApi;

/** @var Core\Routing\Router $router */

$router->group(['prefix' => 'api/v1', 'middleware' => AuthenticateApi::class], function ($router) {

    $router->get('/user', function (Core\Http\Request $request) {
        // Auth facade or helper can now be used
        if (Core\Auth\Auth::check()) {
            return new \App\Http\Resources\UserResource(Core\Auth\Auth::user());
        }
        return response()->json(['message' => 'Error fetching user'], 500); // Should not happen if middleware ran
    });

    // Other protected API routes...
    // $router->get('/posts', [\App\Controllers\Api\PostController::class, 'index']);
    $router->get('/posts', [\App\Controllers\Api\AuthController::class, 'index']);
});

// Public API routes can go outside the group
$router->post('/api/v1/login', [\App\Controllers\Api\AuthController::class, 'login']); // Handles login/token issuance
