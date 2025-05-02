<?php

namespace Core\View;

use Exception;
use Throwable;

class ViewEngine
{
    protected string $viewsPath;

    /**
     * Initialize the view engine.
     *
     * @param string $viewsPath Directory containing view files.
     */
    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/\\');
    }

    /**
     * Render a view template.
     *
     * @param string $viewName Dot notation for view file (e.g., 'home.index').
     * @param array $data Data to extract for the view.
     * @return string The rendered view content.
     * @throws Exception If the view file is not found.
     * @throws Throwable If an error occurs during view rendering.
     */
    public function render(string $viewName, array $data = []): string
    {
        $file = $this->resolveViewPath($viewName);

        if (!file_exists($file)) {
            throw new Exception("View file not found: {$file}");
        }

        // Extract data variables into the local scope for the view file
        // Using EXTR_SKIP prevents overwriting existing variables like $this
        extract($data, EXTR_SKIP);

        // Start output buffering
        ob_start();

        try {
            // Include the view file. $this within the view file will refer
            // to this ViewEngine instance, which might not be what you want.
            // If you need $this in views to refer to something else,
            // consider using a different rendering approach or a template engine.
            include $file;
        } catch (Throwable $e) {
            ob_end_clean(); // Discard buffer content on error
            // You might want to log this error as well
            throw $e; // Re-throw the error/exception from the view
        }

        // Get the captured output and end buffering
        return ob_get_clean();
    }

    /**
     * Resolve the full path to a view file.
     *
     * @param string $viewName Dot notation view name.
     * @return string Full file path.
     */
    protected function resolveViewPath(string $viewName): string
    {
        $filePath = str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.php';
        return $this->viewsPath . DIRECTORY_SEPARATOR . $filePath;
    }
}
