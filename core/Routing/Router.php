<?php

namespace Core\Routing;

use Closure;
use Exception;
use Throwable;
use Core\Logging\Log;
use Core\Http\Request;
use Core\Http\Response\Response;
use Core\Exceptions\HttpException;
use Core\View\ViewEngineInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Router
{
    protected array $routes = [];
    protected Request $request;
    protected Response $response; // Inject Response later

    protected array $groupStack = []; // For prefixing and middleware groups

    public function __construct(Request $request)
    { // Add Response $response later
        $this->request = $request;
        // $this->response = $response;
    }

    // Methods to add routes
    public function get(string $uri, array|Closure $action): Route
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array|Closure $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, array|Closure $action): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, array|Closure $action): Route
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, array|Closure $action): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    // Grouping functionality
    public function group(array $attributes, Closure $callback): void
    {
        $this->groupStack[] = $attributes; // Push group attributes onto stack
        $callback($this); // Execute the callback, routes inside will use the current group context
        array_pop($this->groupStack); // Pop group attributes off stack
    }

    protected function addRoute(string $method, string $uri, array|Closure $action): Route
    {
        $prefix = $this->getCurrentPrefix();
        $uri = rtrim($prefix, '/') . '/' . ltrim($uri, '/');
        $uri = $uri === '/' ? '/' : rtrim($uri, '/'); // Normalize URI

        $route = new Route($method, $uri, $action);
        $this->applyGroupMiddleware($route); // Apply middleware from group stack

        $this->routes[$method][$uri] = $route;
        return $route; // Return Route object for chaining middleware, etc.
    }

    protected function getCurrentPrefix(): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            $prefix .= '/' . ($group['prefix'] ?? '');
        }
        return trim($prefix, '/');
    }

    protected function applyGroupMiddleware(Route $route): void
    {
        $middlewares = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $groupMiddleware = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $middlewares = array_merge($middlewares, $groupMiddleware);
            }
        }
        if (!empty($middlewares)) {
            $route->middleware($middlewares);
        }
    }


    public function dispatch(string $uri, string $method): SymfonyResponse
    {
        try {
            $route = $this->findRoute($uri, $method);

            if (!$route) {
                return $this->renderErrorResponse(404);
            }

            $handler = $this->createMiddlewarePipeline($route->getMiddleware(), function (Request $request) use ($route) {
                return $this->resolveRouteAction($route, $request);
            });

            $result = $handler($this->request);

            // Ensure the result is a SymfonyResponse object
            if ($result instanceof SymfonyResponse) {
                return $result;
            }

            // Convert other return types to SymfonyResponse
            return new SymfonyResponse($result);
        } catch (HttpException $e) {
            return $this->renderErrorResponse($e->getStatusCode(), $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Route dispatch failed: ' . $e->getMessage(), ['exception' => $e]);
            return $this->renderErrorResponse(500, 'Internal Server Error');
        }
    }

    protected function renderErrorResponse(int $statusCode, string $message = ''): SymfonyResponse
    {
        try {
            $viewEngine = app(ViewEngineInterface::class);
            $errorView = "errors.{$statusCode}";

            if (!$viewEngine->exists($errorView)) {
                $errorView = 'errors.default';
            }

            $content = $viewEngine->render($errorView, [
                'error' => [
                    'code' => $statusCode,
                    'message' => $message ?: $this->getDefaultErrorMessage($statusCode)
                ]
            ]);

            return new SymfonyResponse($content, $statusCode);
        } catch (Throwable $e) {
            Log::error('Failed to render error page: ' . $e->getMessage());
            return new SymfonyResponse(
                $this->getFallbackErrorContent($statusCode, $message),
                $statusCode
            );
        }
    }

    protected function getDefaultErrorMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request',
            404 => 'Page Not Found',
            419 => 'Page Expired',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

        return $messages[$statusCode] ?? 'An error occurred';
    }

    protected function getFallbackErrorContent(int $statusCode, string $message): string
    {
        return sprintf(
            '<!DOCTYPE html><html><head><title>%d %s</title></head><body><h1>%d %s</h1><p>%s</p></body></html>',
            $statusCode,
            $this->getDefaultErrorMessage($statusCode),
            $statusCode,
            $this->getDefaultErrorMessage($statusCode),
            htmlspecialchars($message)
        );
    }

    protected function findRoute(string $uri, string $method): ?Route
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        // Direct match first (faster)
        if (isset($this->routes[$method][$uri])) {
            return $this->routes[$method][$uri];
        }

        // Check for routes with parameters (e.g., /users/{id})
        foreach ($this->routes[$method] as $routeUri => $route) {
            if (str_contains($routeUri, '{')) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $routeUri);
                $pattern = '#^' . $pattern . '$#'; // Add delimiters and anchors

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove the full match
                    $route->setParameters($this->extractRouteParameters($routeUri, $matches));
                    return $route;
                }
            }
        }

        return null;
    }

    protected function extractRouteParameters(string $routeUri, array $matches): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $routeUri, $paramNames);
        if (count($paramNames[1]) === count($matches)) {
            return array_combine($paramNames[1], $matches);
        }
        return []; // Should not happen if regex matched correctly
    }


    protected function resolveRouteAction(Route $route, Request $request): mixed
    {
        $action = $route->getAction();
        $params = $route->getParameters();

        if ($action instanceof Closure) {
            // Call the closure with parameters injected (basic example)
            $reflection = new \ReflectionFunction($action);
            $args = $this->resolveMethodDependencies($reflection, $params, $request);
            return call_user_func_array($action, $args);
        }

        if (is_array($action) && count($action) === 2 && is_string($action[0]) && is_string($action[1])) {
            [$controllerClass, $method] = $action;

            // Ensure controller namespace (adjust if needed)
            if (!str_starts_with($controllerClass, 'App\\Controllers\\')) {
                $controllerClass = 'App\\Controllers\\' . $controllerClass;
            }

            if (!class_exists($controllerClass)) {
                throw new Exception("Controller class {$controllerClass} not found.");
            }

            $controller = new $controllerClass(); // Consider dependency injection later

            if (!method_exists($controller, $method)) {
                throw new Exception("Method {$method} not found in controller {$controllerClass}.");
            }

            // Call the controller method with parameters injected
            $reflection = new \ReflectionMethod($controller, $method);
            $args = $this->resolveMethodDependencies($reflection, $params, $request);
            return call_user_func_array([$controller, $method], $args);
        }

        throw new Exception("Invalid route action definition.");
    }

    // Basic dependency injection for route parameters and Request
    protected function resolveMethodDependencies(\ReflectionFunctionAbstract $reflection, array $routeParams, Request $request): array
    {
        $args = [];
        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if (isset($routeParams[$paramName])) {
                // Basic type juggling (improve as needed)
                $value = $routeParams[$paramName];
                if ($paramType instanceof \ReflectionNamedType && $paramType->getName() === 'int') {
                    $value = (int)$value;
                }
                $args[] = $value;
            } elseif ($paramType instanceof \ReflectionNamedType && $paramType->getName() === Request::class) {
                $args[] = $request; // Inject the Request object
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                // Could potentially resolve other dependencies from a container here
                throw new Exception("Could not resolve parameter '{$paramName}' for route.");
            }
        }
        return $args;
    }

    // Middleware Pipeline
    protected function createMiddlewarePipeline(array $middlewares, Closure $final): Closure
    {
        $pipeline = $final;

        foreach (array_reverse($middlewares) as $middleware) {
            $pipeline = function (Request $request) use ($middleware, $pipeline) {
                // Resolve middleware instance (assuming class names for now)
                if (!str_starts_with($middleware, 'App\\Middleware\\')) {
                    $middleware = 'App\\Middleware\\' . $middleware;
                }

                if (!class_exists($middleware)) {
                    throw new Exception("Middleware class {$middleware} not found.");
                }
                $instance = new $middleware(); // Consider DI

                // Check if middleware implements a specific interface if desired
                // if (! $instance instanceof MiddlewareInterface) ...

                return $instance->handle($request, $pipeline);
            };
        }

        return $pipeline;
    }

    protected function handleNotFound(): mixed
    {
        http_response_code(404);
        // You could return a specific view or simple message
        // return view('errors.404'); // Using the view helper
        return '404 Not Found';
    }
}

// Route class to hold route details
class Route
{
    protected string $method;
    protected string $uri;
    protected array|Closure $action;
    protected array $parameters = [];
    protected array $middleware = [];

    public function __construct(string $method, string $uri, array|Closure $action)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;
    }

    public function middleware(string|array $middleware): self
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        // Prepend to run group middleware first, then route-specific
        $this->middleware = array_merge($this->middleware, $middleware);
        // Use array_unique if needed
        return $this; // Allow chaining
    }

    // Getters
    public function getMethod(): string
    {
        return $this->method;
    }
    public function getUri(): string
    {
        return $this->uri;
    }
    public function getAction(): array|Closure
    {
        return $this->action;
    }
    public function getParameters(): array
    {
        return $this->parameters;
    }
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    // Setter for parameters matched by the router
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
