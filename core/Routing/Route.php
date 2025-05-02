<?php

namespace Core\Routing;

use Closure;

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
