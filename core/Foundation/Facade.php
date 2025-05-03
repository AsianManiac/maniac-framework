<?php

namespace Core\Foundation;

use RuntimeException;
use Core\Foundation\App;
use Core\Logging\Log;

/**
 * Provides a static proxy to objects bound in the service container (App registry).
 */
abstract class Facade
{
    /**
     * The application instance being facaded.
     *
     * @var App|null
     */
    protected static ?App $app = null;

    /**
     * The resolved object instances.
     *
     * @var array<string, object>
     */
    protected static array $resolvedInstance = [];

    /**
     * Set the application instance used by the facade.
     *
     * @param App $app
     * @return void
     */
    public static function setFacadeApplication(App $app): void
    {
        static::$app = $app;
    }

    /**
     * Get the application instance used by the facade.
     *
     * @return App
     * @throws RuntimeException
     */
    protected static function getFacadeApplication(): App
    {
        if (!static::$app) {
            $message = 'Facade application instance has not been set. Ensure Facade::setFacadeApplication() is called during bootstrap.';
            Log::error($message);
            throw new RuntimeException($message);
        }
        return static::$app;
    }

    /**
     * Get the registered name (key) of the component in the container.
     *
     * @return string
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        throw new RuntimeException(static::class . ' does not implement getFacadeAccessor method.');
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @param string $name The accessor key.
     * @return mixed The resolved service instance.
     */
    protected static function resolveFacadeInstance(string $name): mixed
    {
        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        $app = static::getFacadeApplication();

        try {
            $instance = $app::resolve($name);
            static::$resolvedInstance[$name] = $instance;
            return $instance;
        } catch (\Throwable $e) {
            Log::error("Could not resolve facade instance for [{$name}]: " . $e->getMessage(), ['exception' => $e]);
            throw new RuntimeException("Could not resolve facade instance for [{$name}]: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Clear a resolved facade instance.
     *
     * @param string $name The accessor key.
     * @return void
     */
    public static function clearResolvedInstance(string $name): void
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all resolved instances.
     *
     * @return void
     */
    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstance = [];
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method The method being called.
     * @param array $args The arguments passed to the method.
     * @return mixed The result of the method call.
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $accessor = static::getFacadeAccessor();
        $instance = static::resolveFacadeInstance($accessor);

        if (!$instance) {
            $message = "A facade root has not been set for '{$accessor}'.";
            Log::error($message);
            throw new RuntimeException($message);
        }

        if (!method_exists($instance, $method)) {
            $message = sprintf(
                'Call to undefined method %s::%s() on facade %s',
                get_class($instance),
                $method,
                static::class
            );
            Log::error($message);
            throw new RuntimeException($message);
        }

        return $instance->{$method}(...$args);
    }
}
