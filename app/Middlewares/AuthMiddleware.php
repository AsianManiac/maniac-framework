<?php

namespace App\Middleware;

use Closure;
use Core\Http\Request;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next The next middleware or the route action
     * @return mixed Response or result from $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Example: Check if user is logged in (implement session/auth logic)
        $isLoggedIn = $_SESSION['user_id'] ?? false; // Very basic example

        if (!$isLoggedIn) {
            // Redirect to login page (requires Response class/helper)
            // return redirect('/login');
            http_response_code(403); // Forbidden
            return "Access Denied. Please log in."; // Simple response for now
        }

        // User is authenticated, pass the request to the next middleware or route handler
        return $next($request);
    }
}
