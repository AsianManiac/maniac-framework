<?php

use Core\Cookie\CookieJar;

if (!function_exists('cookie')) {
    /**
     * Create a new cookie instance or retrieve the cookie jar.
     *
     * cookie('name', 'value', 60); // Creates & queues a 60-min cookie
     * cookie()->forever('name', 'value'); // Use CookieJar methods
     * cookie()->forget('name'); // Queue a forget cookie
     * request()->cookie('name'); // Get cookie value from request
     *
     * @param string|null $name Cookie name or null to get the Jar.
     * @param string $value
     * @param int $minutes
     * @param ... other make() params
     * @return \Symfony\Component\HttpFoundation\Cookie|CookieJar|void
     */
    function cookie(
        ?string $name = null,
        string $value = '',
        int $minutes = 0,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        ?bool $httpOnly = null,
        bool $raw = false,
        ?string $sameSite = null
    ) {
        /** @var CookieJar $jar */
        $jar = app(CookieJar::class);

        if (is_null($name)) {
            return $jar; // Return the Jar instance for chaining
        }

        // If name provided, assume we are queuing a cookie
        $jar->queue($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        // Doesn't return the cookie instance when queuing via helper
    }
}
