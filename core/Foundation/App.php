<?php

namespace Core\Foundation;

use Closure;
use Exception;
use Core\Logging\Log;
use Core\Http\Request;
use Core\Routing\Router;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Core\Http\Response\Response;

/**
 * Core application class for the Maniac Framework.
 *
 * Acts as a service container and handles request dispatching.
 */
class App
{
    /**
     * The registry of services and factories.
     *
     * @var array<string, mixed>
     */
    private static array $registry = [];

    /**
     * Bind a key to a value or factory.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function bind(string $key, mixed $value): void
    {
        self::$registry[$key] = $value;
    }

    /**
     * Resolve a service from the registry.
     *
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public static function resolve(string $key): mixed
    {
        if (!isset(self::$registry[$key])) {
            throw new Exception("No instance found in registry for key: {$key}");
        }

        $instanceOrFactory = self::$registry[$key];

        if ($instanceOrFactory instanceof Closure) {
            $resolvedInstance = $instanceOrFactory();
            self::$registry[$key] = $resolvedInstance;
            return $resolvedInstance;
        }

        return $instanceOrFactory;
    }

    /**
     * Get the bound value without resolving factories.
     *
     * @param string $key
     * @return mixed|null
     */
    public static function instance(string $key): mixed
    {
        return self::$registry[$key] ?? null;
    }

    /**
     * Check if a key is bound.
     *
     * @param string $key
     * @return bool
     */
    public static function bound(string $key): bool
    {
        return isset(self::$registry[$key]);
    }

    /**
     * Check if the application is running in production.
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return config('app.env', 'local') === 'production';
    }

    /**
     * Check if the application is running locally.
     *
     * @return bool
     */
    public static function isLocal(): bool
    {
        return config('app.env', 'local') === 'local';
    }

    /**
     * Prohibit destructive commands in production.
     *
     * @param string $command
     * @return void
     * @throws Exception
     */
    public static function prohibitDestructiveCommands(string $command): void
    {
        if (self::isProduction() && in_array($command, ['migrate:rollback', 'db:wipe'])) {
            throw new Exception("Destructive command '{$command}' is prohibited in production.");
        }
    }

    /**
     * Get the application environment.
     *
     * @return string
     */
    public static function environment(): string
    {
        return config('app.env', 'local');
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @return void
     */
    public function handleRequest(): void
    {
        try {

            /** @var \Core\Http\Request $request */
            $request = Request::createFromGlobals();
            $router = new Router($request);

            require __DIR__ . '/../../routes/web.php';

            $response = $router->dispatch($request->uri(), $request->method());
            if ($response instanceof SymfonyResponse) {

                $response->send();
            } else {
                throw new Exception('Router did not return a valid Response object');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle an exception.
     *
     * @param Exception $e
     * @return void
     */
    protected function handleException(Exception $e): void
    {
        Log::error('Application error', ['exception' => $e]);

        if (config('app.debug', false)) {
            try {
                $response = response()->view('errors.debug', [
                    'error' => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ],
                ], 500);
            } catch (Exception $viewError) {
                Log::error('Failed to render debug error view: ' . $viewError->getMessage(), ['exception' => $viewError]);
                $response = new Response(
                    '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>',
                    500
                );
            }
        } else {
            try {
                $response = response()->view('errors.500', [], 500);
            } catch (Exception $viewError) {
                Log::error('Failed to render error view: ' . $viewError->getMessage(), ['exception' => $viewError]);
                $response = new Response('Internal Server Error', 500);
            }
        }

        $response->send();
    }
}
