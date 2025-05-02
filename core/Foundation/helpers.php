<?php

use Core\Logging\Log;
use Core\Foundation\App;
use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        // Simple env retriever, vlucas/phpdotenv handles the loading
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        // Basic config loader (improve with caching maybe)
        static $config = [];
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $filePath = realpath(__DIR__ . '/../../config/' . $file . '.php');

        if (!isset($config[$file]) && $filePath && file_exists($filePath)) {
            $config[$file] = require $filePath;
        }

        $value = $config[$file] ?? null;
        foreach ($parts as $part) {
            if (!is_array($value) || !isset($value[$part])) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value ?? $default;
    }
}

// In core/Foundation/helpers.php:
if (!function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     */
    function view(string $viewName, array $data = []): string
    {
        try {
            /** @var Core\View\ViewEngineInterface $engine */ // Use interface now
            $engine = app(Core\View\ViewEngineInterface::class); // Resolve via interface
            return $engine->render($viewName, $data);
        } catch (\Throwable $e) {
            // Log the original error first
            Core\Logging\Log::error("View rendering failed for view [{$viewName}]: " . $e->getMessage(), ['exception' => $e]);

            if (config('app.debug', false)) {
                // In debug mode, re-throwing might still be best for developers
                throw $e;
            }

            // In production, try to show a nice error page
            try {
                /** @var Core\View\ViewEngineInterface $engine */
                $engine = app(Core\View\ViewEngineInterface::class);
                // Render your dedicated error view. Pass minimal or no data.
                // Make sure 'errors.500' corresponds to 'resources/views/errors/500.niac.php'
                return $engine->render('errors.500', []);
            } catch (\Throwable $errorViewException) {
                // If rendering the error view ALSO fails, log that too and return plain text
                Core\Logging\Log::error("Failed to render error view: " . $errorViewException->getMessage(), ['exception' => $errorViewException]);
                return "An unexpected error occurred. Please try again later."; // Fallback text
            }
        }
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance or resolve an abstract.
     */
    function app(?string $abstract = null)
    {
        if (is_null($abstract)) {
            return App::instance('app'); // Assuming you bind the App/Container itself if needed
        }
        try {
            return App::resolve($abstract);
        } catch (\Throwable $e) {
            // Handle cases where it might not be bound yet depending on bootstrap phase
            // Maybe return null or throw a different exception?
            throw $e;
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the given variables and end the script.
     * Uses VarDumper if available for nicer output.
     */
    function dd(...$vars): void
    {
        Log::debug('dd() called', ['location' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]]);

        // If headers not sent and not running in CLI, output pre tag
        if (!headers_sent() && php_sapi_name() !== 'cli') {
            echo '<pre>';
        }

        if (class_exists(VarDumper::class)) {
            // Use Symfony VarDumper
            foreach ($vars as $v) {
                VarDumper::dump($v);
            }
        } else {
            // Fallback to var_dump
            foreach ($vars as $v) {
                var_dump($v);
            }
        }

        if (!headers_sent() && php_sapi_name() !== 'cli') {
            echo '</pre>';
        }

        die(1);
    }
}

// Note: `compact()` is a built-in PHP function. No need to redefine.
// Usage: view('some.view', compact('users', 'posts'))

if (!function_exists('asset')) {
    /**
     * Generate a URL for an asset.
     *
     * @param string $path The asset path (e.g., 'css/app.css').
     * @return string The full asset URL.
     */
    function asset(string $path): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        $path = ltrim($path, '/');
        return $baseUrl . '/assets/' . $path;
    }
}

if (!function_exists('app')) {
    /**
     *
     */
    function app(?string $abstract = null): mixed
    {
        if (is_null($abstract)) {
            return App::instance('app');
        }
        try {
            return App::resolve($abstract);
        } catch (\Throwable $e) {
            Core\Logging\Log::error("Failed to resolve '{$abstract}': " . $e->getMessage(), ['exception' => $e]);
            throw new Exception("Failed to resolve '{$abstract}': {$e->getMessage()}");
        }
    }
}

// Include all helper files from the 'helpers' subdirectory
foreach (glob(__DIR__ . '/helpers/*.php') as $filename) {
    require_once $filename;
}

 // Add other helpers like `session()`, `auth()`, `csrf_token()` etc. as you build those features.
