<?php

namespace Core\Logging;

use RuntimeException;
use Core\Foundation\App;
use Psr\Log\LoggerInterface;

/**
 * @method static void emergency(string|\Stringable $message, array $context = [])
 * @method static void alert(string|\Stringable $message, array $context = [])
 * @method static void critical(string|\Stringable $message, array $context = [])
 * @method static void error(string|\Stringable $message, array $context = [])
 * @method static void warning(string|\Stringable $message, array $context = [])
 * @method static void notice(string|\Stringable $message, array $context = [])
 * @method static void info(string|\Stringable $message, array $context = [])
 * @method static void debug(string|\Stringable $message, array $context = [])
 * @method static void log($level, string|\Stringable $message, array $context = [])
 * @method static LoggerInterface channel(string $name)
 *
 * @see \Core\Logging\LogManager
 * @see \Psr\Log\LoggerInterface
 */
class Log
{
    protected static ?LogManager $manager = null;

    public static function setLogManager(LogManager $manager): void
    {
        static::$manager = $manager;
    }

    protected static function getManager(): LogManager
    {
        if (!static::$manager) {
            // Attempt to build it if not set (requires App registry/container)
            try {
                static::$manager = App::resolve(LogManager::class); // Requires App::resolve()
            } catch (\Throwable $e) {
                throw new RuntimeException('LogManager has not been instantiated. Bootstrap error? ' . $e->getMessage());
            }
        }
        if (!static::$manager) { // Check again after trying to resolve
            throw new RuntimeException('LogManager could not be resolved or instantiated.');
        }
        return static::$manager;
    }

    /**
     * Handle dynamic static calls to the facade.
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        $manager = static::getManager();

        // Allow Log::channel('name')->info(...)
        if ($method === 'channel') {
            return $manager->channel(...$args);
        }

        // Call method on the default channel instance
        return $manager->channel()->{$method}(...$args);
    }
}
