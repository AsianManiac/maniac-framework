<?php

namespace Core\Cookie;

use Core\Foundation\Facade;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

/**
 * @method static SymfonyCookie make(string $name, string $value, int $minutes = 0, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = null, bool $raw = false, string $sameSite = null)
 * @method static SymfonyCookie forever(string $name, string $value, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = null, bool $raw = false, string $sameSite = null)
 * @method static SymfonyCookie forget(string $name, string $path = null, string $domain = null)
 * @method static void queue(...$parameters)
 * @method static void unqueue(string $name)
 * @method static bool hasQueued(string $name)
 * @method static SymfonyCookie|null getQueued(string $name, $default = null)
 * @method static array<string, SymfonyCookie> getQueuedCookies()
 *
 * @see \Core\Cookie\CookieJar
 */
class Cookie extends Facade
{
    /**
     * Get the registered name of the component in the container.
     */
    protected static function getFacadeAccessor(): string
    {
        return CookieJar::class; // Bind CookieJar in App container
    }
}
