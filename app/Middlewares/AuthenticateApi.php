<?php

namespace App\Middleware;

use Closure;
use App\Models\User;
use Core\Http\Request;
use Core\Auth\AuthManager;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{

    protected AuthManager $auth;

    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request for API authentication.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed Response or result from $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        // Hash the incoming token using the *same* method used for storage comparison
        $hashedToken = hash('sha256', $token);

        // Find user by the HASHED token
        $user = User::where('api_token', '=', $hashedToken)->first(); // Assumes Model where() exists

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        // Set the authenticated user for the current request
        $this->auth->setUser($user);

        // Proceed to the next middleware or route handler
        return $next($request);
    }
}
