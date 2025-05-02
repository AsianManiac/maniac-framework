<?php

use Core\Csrf\Csrf; // Assume a CSRF class/service exists
use Core\View\ViewEngineInterface;

// if (!function_exists('view')) {
//     /**
//      * Get the evaluated view contents for the given view.
//      * Now uses the Niac engine implicitly via the container binding.
//      *
//      * @param string $viewName The view name (e.g., 'pages.home').
//      * @param array $data Data to pass to the view.
//      * @return string Rendered HTML.
//      */
//     function view(string $viewName, array $data = []): string
//     {
//         try {
//             /** @var ViewEngineInterface $engine */
//             $engine = app(ViewEngineInterface::class); // Resolve via interface
//             return $engine->render($viewName, $data);
//         } catch (\Throwable $e) {
//             // Handle view rendering errors (keep existing logic or refine)
//             if (config('app.debug', false)) {
//                 throw $e;
//             }
//             Core\Logging\Log::error("View rendering failed: " . $e->getMessage(), ['exception' => $e]);
//             return "Error rendering view.";
//         }
//     }
// }


if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     * Points to the public directory.
     *
     * @param string $path Path relative to the public directory (e.g., 'css/app.css').
     * @return string Fully qualified asset URL.
     */
    function asset(string $path): string
    {
        // Use asset_url if defined (for CDN etc.), otherwise app.url
        $baseUrl = rtrim(config('app.asset_url', config('app.url', '')), '/');
        // Remove leading slash from path to prevent double slashes
        $path = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token input field.
     * Requires a CSRF service bound in the container.
     *
     * @return string HTML input field.
     */
    function csrf_field(): string
    {
        try {
            /** @var Csrf $csrf */ // Assuming you have a Core\Csrf\Csrf class
            $csrf = app(Csrf::class);
            $token = $csrf->getToken();
            $name = $csrf->getTokenName(); // Method to get the expected input name
            return '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($token) . '">';
        } catch (\Throwable $e) {
            // Log error or handle gracefully if CSRF service not set up
            Core\Logging\Log::warning('CSRF field generation failed. Service not available?', ['exception' => $e]);
            return '<!-- CSRF field unavailable -->';
        }
    }
}

// Add url() helper if not already present
if (!function_exists('url')) {
    /**
     * Generate a fully qualified URL to the given path.
     *
     * @param string $path Relative path (e.g., '/users').
     * @return string Fully qualified URL.
     */
    function url(string $path): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        $path = '/' . ltrim($path, '/'); // Ensure leading slash
        return $baseUrl . $path;
    }
}
