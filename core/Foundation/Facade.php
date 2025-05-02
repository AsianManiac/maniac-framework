<?php

namespace Core\Foundation;

use Mockery;
use RuntimeException;
use Core\Foundation\App;

/**
 * Provides a static proxy to objects bound in the service container (App registry).
 */
abstract class Facade
{
    /**
     * The application instance being facaded.
     * We use our simple App registry here.
     * @var App|null // Or your ContainerInterface if you adopt one
     */
    protected static ?App $app = null; // Can be set during bootstrap

    /**
     * The resolved object instances.
     * Cache resolved instances for performance.
     * @var array<string, object>
     */
    protected static array $resolvedInstance = [];

    /**
     * Set the application instance used by the facade.
     * Call this during bootstrap.
     *
     * @param App $app // Or your ContainerInterface
     * @return void
     */
    public static function setFacadeApplication(App $app): void
    {
        static::$app = $app;
    }

    /**
     * Get the application instance used by the facade.
     *
     * @return App // Or your ContainerInterface
     * @throws RuntimeException
     */
    protected static function getFacadeApplication(): App
    {
        if (!static::$app) {
            // Attempt to resolve App if not explicitly set (basic fallback)
            // This assumes App itself is somehow globally accessible or resolvable
            // It's better to explicitly call setFacadeApplication in bootstrap.
            if (class_exists(App::class) && method_exists(App::class, 'getInstance')) {
                static::$app = App::getInstance(); // Hypothetical getInstance method
            }
            if (!static::$app) {
                throw new RuntimeException('Facade application instance has not been set.');
            }
        }
        return static::$app;
    }

    /**
     * Get the registered name (key) of the component in the container.
     * Each concrete facade MUST implement this method.
     *
     * @return string The key used with App::bind() / App::resolve()
     * @throws \RuntimeException
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
            // Use the container (App registry) to resolve the instance
            $instance = $app::resolve($name); // Assuming App::resolve is static
            static::$resolvedInstance[$name] = $instance;
            return $instance;
        } catch (\Throwable $e) {
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
     * @throws \RuntimeException If the facade root cannot be resolved or method doesn't exist.
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $accessor = static::getFacadeAccessor();
        $instance = static::resolveFacadeInstance($accessor);

        if (!$instance) {
            throw new RuntimeException("A facade root has not been set for '{$accessor}'.");
        }

        // Check if method exists on the resolved instance
        if (!method_exists($instance, $method)) {
            throw new RuntimeException(sprintf(
                'Call to undefined method %s::%s() on facade %s',
                get_class($instance),
                $method,
                static::class // Added facade class name for clarity
            ));
        }

        // Forward the call to the resolved instance
        return $instance->{$method}(...$args);
    }
}
