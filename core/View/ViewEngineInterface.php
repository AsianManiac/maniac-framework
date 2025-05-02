<?php

/**
 * Interface for View Engines in the Maniac Framework.
 *
 * Defines the contract for view rendering engines, ensuring they provide a
 * method to render templates with data.
 */

namespace Core\View;

interface ViewEngineInterface
{
    /**
     * Render a view template.
     *
     * @param string $viewName The view name (e.g., dot notation).
     * @param array $data Data to pass to the view.
     * @return string The rendered content.
     */
    public function render(string $viewName, array $data = []): string;

    /**
     *
     */
    public function exists(string $viewName): bool;
}
