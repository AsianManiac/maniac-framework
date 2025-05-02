<?php

namespace App\Http\Middleware;

use Closure;
use Core\Http\Request;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Add middleware logic here
        return $next($request);
    }
}