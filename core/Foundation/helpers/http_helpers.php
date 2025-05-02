<?php

use Core\Http\Request;
use Core\Session\SessionManager;
use Core\Http\Response\RedirectResponse;
use Core\Http\Response\ResponseFactoryInterface;


if (!function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * Examples:
     * request(); // Returns the Core\Http\Request instance
     * request('name', 'default'); // Returns input value for 'name' or 'default'
     * request(['name', 'email']); // Returns an array ['name' => value, 'email' => value]
     *
     * @param array|string|null $key The input key(s) or null to get the Request instance.
     * @param mixed $default The default value if the key is not found (for single key).
     * @return Request|array|string|null
     * @throws Exception if Request class cannot be resolved.
     */
    function request(array|string|null $key = null, mixed $default = null): Request|array|string|null
    {
        $requestInstance = app(Request::class); // Assumes Request is bound
        if (is_null($key)) {
            return $requestInstance;
        }
        if (is_array($key)) {
            // Implement ->only() or ->inputs() on Request class for cleaner implementation
            $data = [];
            $all = $requestInstance->all();
            foreach ($key as $k) {
                $data[$k] = $all[$k] ?? ($default[$k] ?? null); // Allow default per key in array
            }
            return $data;
        }
        return $requestInstance->input($key, $default);
    }
}

if (!function_exists('response')) {
    /**
     * Get an instance of the response factory.
     * Allows chaining methods like: response()->json(['foo' => 'bar']);
     * Or directly creating a simple response: response('Hello World', 200);
     *
     * @param string|null $content Content for a simple response.
     * @param int $status Status code for a simple response.
     * @param array $headers Headers for a simple response.
     * @return ResponseFactoryInterface|\Symfony\Component\HttpFoundation\Response
     * @throws Exception if ResponseFactoryInterface cannot be resolved.
     */
    function response(?string $content = null, int $status = 200, array $headers = []): ResponseFactoryInterface|\Symfony\Component\HttpFoundation\Response
    {
        /** @var ResponseFactoryInterface $factory */
        $factory = app(ResponseFactoryInterface::class); // Resolve the factory
        if (func_num_args() === 0) {
            return $factory; // Return factory for chaining: response()->json(...)
        }
        // Create a simple response directly if content is provided
        return $factory->make($content ?? '', $status, $headers);
    }
}

if (!function_exists('redirect')) {
    /**
     * Get an instance of the redirect response.
     *
     * @param string|null $to The URL to redirect to. Null to return the Redirector instance (if you build one).
     * @param int $status The HTTP status code.
     * @param array $headers Custom headers.
     * @return RedirectResponse If $to is provided.
     * @throws Exception if ResponseFactoryInterface cannot be resolved.
     */
    function redirect(?string $to = null, int $status = 302, array $headers = []): RedirectResponse
    {
        /** @var ResponseFactoryInterface $factory */
        $factory = app(ResponseFactoryInterface::class);
        // If you build a Redirector class later for ->with(), you'd return it here if $to is null
        if (is_null($to)) {
            // For now, require a URL
            throw new InvalidArgumentException('Redirect URL must be provided.');
        }
        return $factory->redirect($to, $status, $headers);
    }
}

if (!function_exists('session')) {
    /**
     * Get / set session values.
     *
     * Usage:
     * session(); // Returns SessionManager instance
     * session('key'); // Get value for 'key'
     * session('key', 'default'); // Get value or default
     * session(['key' => 'value']); // Set multiple values
     *
     * @param array|string|null $key
     * @param mixed $default
     * @return mixed|\Core\Session\SessionManager
     */
    function session(array|string|null $key = null, mixed $default = null): mixed
    {
        /** @var \Core\Session\SessionManager $manager */
        $manager = app(SessionManager::class);

        if ($key === null) {
            return $manager; // Return manager for methods like flash() etc.
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $manager->set($k, $v);
            }
            return null; // Or maybe return $manager? Your choice.
        }

        return $manager->get($key, $default);
    }
}
