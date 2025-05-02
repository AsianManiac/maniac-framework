<?php

namespace Core\Mvc;

use Core\Http\Response\Response;
use Core\Http\Response\RedirectResponse;


// Base Controller - Can add shared logic, helpers, or properties here
abstract class Controller
{
    // Example: Method to easily return a view
    protected function view(string $viewName, array $data = []): string
    {
        // Delegate to a global helper or View class instance
        return view($viewName, $data);
    }

    protected function json(array $data, int $status = 200): Response
    {
        return new Response($data, $status, ['Content-Type' => 'application/json']);
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    // Add other common methods like redirect(), json(), etc.
}
