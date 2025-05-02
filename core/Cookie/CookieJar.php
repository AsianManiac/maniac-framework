<?php

namespace Core\Cookie;

use Exception;
use Core\Encryption\Encrypter;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class CookieJar
{
    protected array $config;
    protected ?Encrypter $encrypter = null;

    /**
     * Queued cookies waiting to be attached to the response.
     * @var array<string, SymfonyCookie>
     */
    protected array $queued = [];

    public function __construct(array $config, ?Encrypter $encrypter = null)
    {
        $this->config = $config;
        if ($this->config['encrypt'] ?? false) {
            if (!$encrypter) {
                // Try resolving if not injected (basic fallback)
                try {
                    $encrypter = app(Encrypter::class);
                } catch (\Throwable $e) {
                }
            }
            if (!$encrypter) {
                throw new Exception("Cookie encryption enabled but no Encrypter service is available.");
            }
            $this->encrypter = $encrypter;
        }
    }

    /**
     * Create a new cookie instance.
     *
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * @param int $minutes Lifetime in minutes (0 for session cookie).
     * @param string|null $path Path override.
     * @param string|null $domain Domain override.
     * @param bool|null $secure Secure override.
     * @param bool|null $httpOnly HttpOnly override.
     * @param bool $raw Whether the cookie value should be sent raw (unencoded).
     * @param string|null $sameSite SameSite override.
     * @return SymfonyCookie
     */
    public function make(
        string $name,
        string $value,
        int $minutes = 0,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        ?bool $httpOnly = null,
        bool $raw = false,
        ?string $sameSite = null
    ): SymfonyCookie {
        $path = $path ?? $this->config['path'];
        $domain = $domain ?? $this->config['domain'];
        $secure = $secure ?? $this->config['secure'];
        $httpOnly = $httpOnly ?? $this->config['http_only'];
        $sameSite = $sameSite ?? $this->config['same_site'];
        $expires = ($minutes === 0) ? 0 : time() + ($minutes * 60);

        // Encrypt value if enabled and not raw
        if ($this->encrypter && !$raw) {
            try {
                $value = $this->encrypter->encrypt($value);
            } catch (Exception $e) {
                // Log encryption error
                throw new Exception("Could not encrypt cookie value: " . $e->getMessage(), 0, $e);
            }
        }

        return new SymfonyCookie($name, $value, $expires, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Create a cookie that lasts "forever" (approx. 5 years).
     */
    public function forever(string $name, string $value, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httpOnly = null, bool $raw = false, ?string $sameSite = null): SymfonyCookie
    {
        return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $raw, $sameSite); // 5 years in minutes
    }

    /**
     * Create a cookie that expires immediately (used for deleting).
     */
    public function forget(string $name, ?string $path = null, ?string $domain = null): SymfonyCookie
    {
        // Value doesn't matter, expiration is in the past
        return $this->make($name, '', -2628000, $path, $domain);
    }

    /**
     * Queue a cookie to be added to the outgoing response.
     *
     * @param mixed ...$parameters Parameters for the 'make' method.
     * @return void
     */
    public function queue(...$parameters): void
    {
        $cookie = $this->make(...$parameters);
        $this->queued[$cookie->getName()] = $cookie;
    }

    /**
     * Remove a cookie from the queue.
     * Note: This doesn't delete the cookie from the browser, use forget() and queue() for that.
     *
     * @param string $name
     * @return void
     */
    public function unqueue(string $name): void
    {
        unset($this->queued[$name]);
    }

    /**
     * Check if a cookie is queued.
     *
     * @param string $name
     * @return bool
     */
    public function hasQueued(string $name): bool
    {
        return isset($this->queued[$name]);
    }

    /**
     * Get a queued cookie instance.
     *
     * @param string $name
     * @param mixed $default
     * @return SymfonyCookie|null
     */
    public function getQueued(string $name, $default = null): ?SymfonyCookie
    {
        return $this->queued[$name] ?? $default;
    }

    /**
     * Get all queued cookies.
     *
     * @return array<string, SymfonyCookie>
     */
    public function getQueuedCookies(): array
    {
        return $this->queued;
    }

    /**
     * Decrypt a cookie value if encryption is enabled.
     * Returns null if decryption fails.
     *
     * @param string $value Encrypted value.
     * @return string|null Decrypted value or null.
     */
    public function decrypt(string $value): ?string
    {
        if (!$this->encrypter) {
            return $value; // Return raw value if no encrypter
        }
        try {
            return $this->encrypter->decrypt($value);
        } catch (\Throwable $e) {
            // Decryption failed (invalid payload, wrong key, etc.)
            return null;
        }
    }
}
