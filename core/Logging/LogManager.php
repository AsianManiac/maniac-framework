<?php

namespace Core\Logging;

use Exception;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;

class LogManager
{
    protected array $config;
    protected array $channels = []; // Cache instantiated channels
    protected string $defaultChannel;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->defaultChannel = $config['default'] ?? 'file'; // Default to 'file' if not set
    }

    /**
     * Get a log channel instance.
     */
    public function channel(?string $name = null): LoggerInterface
    {
        $name = $name ?? $this->defaultChannel;

        if (isset($this->channels[$name])) {
            return $this->channels[$name];
        }

        if (!isset($this->config['channels'][$name])) {
            throw new InvalidArgumentException("Log channel [{$name}] is not defined.");
        }

        $config = $this->config['channels'][$name];
        $this->channels[$name] = $this->resolve($name, $config);

        return $this->channels[$name];
    }

    /**
     * Resolve the given log instance by name.
     */
    protected function resolve(string $name, array $config): LoggerInterface
    {
        $driver = $config['driver'] ?? null;

        if (!$driver) {
            throw new InvalidArgumentException("Driver not configured for log channel [{$name}].");
        }

        $method = 'create' . ucfirst($driver) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->$method($config);
        } else {
            throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
        }
    }

    // --- Driver Creation Methods ---

    protected function createFileDriver(array $config): LoggerInterface
    {
        if (!isset($config['path'])) {
            throw new InvalidArgumentException("Log path not configured for file driver.");
        }
        // Potentially handle 'daily' logic here or make a separate driver
        // For simplicity, 'daily' uses the same FileLogger but path is pre-calculated in config
        return new Drivers\FileLogger(
            $config['path'],
            $config['level'] ?? 'debug'
        );
        // Add logic for 'days' rotation if needed
    }

    protected function createSyslogDriver(array $config): LoggerInterface
    {
        // Requires implementing a SyslogLogger class or using a library
        // Example: return new Drivers\SyslogLogger($config);
        throw new Exception("Syslog driver not implemented yet.");
        // Placeholder: return new NullLogger();
    }

    protected function createErrorlogDriver(array $config): LoggerInterface
    {
        // Requires implementing an ErrorLogLogger class
        // Example: return new Drivers\ErrorLogLogger($config);
        throw new Exception("Errorlog driver not implemented yet.");
        // Placeholder: return new NullLogger();
    }

    protected function createNullDriver(array $config): LoggerInterface
    {
        return new NullLogger();
    }

    /**
     * Dynamically call the default driver instance.
     * Allows calling $logManager->info(...) which proxies to default channel
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->channel()->{$method}(...$parameters);
    }
}
