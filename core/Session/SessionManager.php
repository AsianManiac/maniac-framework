<?php

namespace Core\Session;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

class SessionManager
{
    protected array $config;
    protected ?SessionInterface $session = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the session instance. Starts the session if not already started.
     *
     * @return SessionInterface
     */
    public function session(): SessionInterface
    {
        if ($this->session === null) {
            $this->session = $this->createSession();
            // Start the session only if it's not already started (Symfony checks this)
            if (!$this->session->isStarted()) {
                $this->session->start();
            }
        }
        return $this->session;
    }

    protected function createSession(): SessionInterface
    {
        $driver = $this->config['driver'] ?? 'file';
        $handler = $this->createHandler($driver);

        // Configure storage options from config
        $options = [
            'cookie_lifetime' => $this->config['lifetime'] * 60, // Convert minutes to seconds
            'cookie_path' => $this->config['path'],
            'cookie_domain' => $this->config['domain'],
            'cookie_secure' => $this->config['secure'],
            'cookie_httponly' => $this->config['http_only'],
            'cookie_samesite' => $this->config['same_site'],
            'name' => $this->config['cookie'],
            // Add gc_probability, gc_divisor, gc_maxlifetime if needed
        ];

        $storage = new NativeSessionStorage($options, $handler, new MetadataBag());

        return new Session($storage);
    }

    protected function createHandler($driver): \SessionHandlerInterface
    {
        return match ($driver) {
            'file' => $this->createFileHandler(),
            'array', 'null' => new NullSessionHandler(), // Use Null for testing/array
            // Add 'database', 'memcached', 'redis' handlers here later
            default => throw new InvalidArgumentException("Unsupported session driver [{$driver}]."),
        };
    }

    protected function createFileHandler(): \SessionHandlerInterface
    {
        $path = $this->config['files'] ?? null;
        if (!$path) {
            throw new InvalidArgumentException("Session file path not configured.");
        }
        // You might use Symfony's handler or PHP's built-in based on config
        return new NativeFileSessionHandler($path);
        // Alternative: return new \SessionHandler(); // If using PHP's native file handler directly
    }

    // --- Add methods to proxy common session actions ---
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->session()->get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        $this->session()->set($key, $value);
    }

    public function has(string $key): bool
    {
        return $this->session()->has($key);
    }

    public function all(): array
    {
        return $this->session()->all();
    }

    public function remove(string $key): mixed
    {
        return $this->session()->remove($key);
    }

    public function clear(): void
    {
        $this->session()->clear();
    }

    public function flash(string $key, mixed $value): void
    {
        $this->session()->getFlashBag()->add($key, $value);
    }

    public function getFlash(string $key, array $default = []): array
    {
        return $this->session()->getFlashBag()->get($key, $default);
    }

    // Add put(), pull(), forget() etc. mirroring Laravel if desired
}
