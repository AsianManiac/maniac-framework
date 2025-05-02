<?php

namespace App\Middleware;

use Closure;
use Core\Http\Request;
use Core\Cookie\CookieJar;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AddQueuedCookiesToResponse
{

    protected CookieJar $cookieJar;

    public function __construct(CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;
    }

    /**
     * Handle an incoming request.
     * Attaches queued cookies to the outgoing response.
     *
     * @param Request $request The Maniac Request wrapper.
     * @param Closure $next
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Process the request and get the response from the next middleware/controller
        $response = $next($request);

        // Attach queued cookies ONLY to Symfony\Component\HttpFoundation\Response instances
        if ($response instanceof SymfonyResponse) {
            foreach ($this->cookieJar->getQueuedCookies() as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }
}
