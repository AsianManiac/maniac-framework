<?php

namespace Core\Http;

use Core\Cookie\CookieJar;
use Core\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request
{
    protected SymfonyRequest $symfonyRequest;

    // Store validated data after validation runs
    protected ?array $validatedData = null;

    /**
     * Create a new request instance.
     * Best practice is to inject the Symfony request.
     *
     * @param SymfonyRequest $symfonyRequest
     */
    public function __construct(SymfonyRequest $symfonyRequest)
    {
        $this->symfonyRequest = $symfonyRequest;
    }

    /**
     * Create a new request instance from PHP globals.
     * Typically used in the bootstrap phase (index.php).
     *
     * @return static
     */
    public static function capture(): static
    {
        // Handle JSON payload automatically if Content-Type is application/json
        SymfonyRequest::enableHttpMethodParameterOverride(); // Allows _method input
        $symfonyRequest = SymfonyRequest::createFromGlobals();

        if (
            str_contains($symfonyRequest->headers->get('CONTENT_TYPE', ''), 'application/json')
            && $symfonyRequest->getContent() !== ''
        ) {
            $data = json_decode($symfonyRequest->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $symfonyRequest->request->replace($data); // Replace POST data with JSON payload
            }
        }

        return new static($symfonyRequest);
    }

    /**
     * Get the underlying Symfony Request instance.
     *
     * @return SymfonyRequest
     */
    public function getSymfonyRequest(): SymfonyRequest
    {
        return $this->symfonyRequest;
    }

    /**
     * Get the full request URI including query string.
     *
     * @return string
     */
    public function url(): string
    {
        return $this->symfonyRequest->getUri();
    }

    /**
     * Get the request URI (same as path for now, can be customized).
     *
     * @return string
     */
    public function uri(): string
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        // $uri = $this->path();
        // Optional: Remove base directory if app is not in web root
        // $baseDir = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
        // if (str_starts_with($uri, $baseDir)) {
        //     $uri = substr($uri, strlen($baseDir));
        // }
        return $uri === '' ? '/' : '/' . $uri; // Ensure leading slash, handle root
    }

    /**
     *
     */
    public function is(string $pattern): bool
    {
        $path = $this->symfonyRequest->getPathInfo();
        return preg_match('#^' . preg_quote($pattern, '#') . '$#', $path);
    }


    /**
     * Get the request path (URI without query string).
     *
     * @return string (e.g., /users/1)
     */
    public function path(): string
    {
        return $this->symfonyRequest->getPathInfo();
    }

    public function method(): string
    {
        // Handle method spoofing for PUT/PATCH/DELETE in forms
        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
            if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                return $method;
            }
        }
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Check if the request method matches the given method.
     *
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->symfonyRequest->isMethod(strtoupper($method));
    }

    /**
     * Get all combined input data (query, request body, json).
     * Prioritizes JSON body, then request body (POST), then query string (GET).
     *
     * @return array
     */
    public function all(): array
    {
        // Combine GET, POST, and potentially JSON body
        $input = $_REQUEST; // Includes GET, POST, COOKIE - might want more specific
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonInput)) {
                $input = array_merge($input, $jsonInput);
            }
        }
        return $input;
        // return array_replace($this->symfonyRequest->query->all(), $this->symfonyRequest->request->all());
    }

    /**
     * Get a specific input item from the request (query, body, json).
     *
     * @param string|null $key The key to retrieve. Null returns all input.
     * @param mixed $default The default value if key not found.
     * @return mixed
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->all();
        }
        // Check request body first (POST/JSON), then query string (GET)
        return $this->symfonyRequest->request->get(
            $key,
            $this->symfonyRequest->query->get($key, $default)
        );
    }

    /**
     * Get an input item from the query string only.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->symfonyRequest->query->all();
        }
        return $this->symfonyRequest->query->get($key, $default);
    }

    /**
     * Get a subset of input data.
     *
     * @param array|string $keys
     * @return array
     */
    public function only(array|string $keys): array
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $all = $this->all();
        $results = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                $results[$key] = $all[$key];
            }
        }
        return $results;
    }

    /**
     * Get all input data except for a specified array of items.
     *
     * @param array|string $keys
     * @return array
     */
    public function except(array|string $keys): array
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = $this->all();
        foreach ($keys as $key) {
            unset($results[$key]);
        }
        return $results;
    }

    /**
     * Check if an input item is present on the request.
     *
     * @param string|array $key
     * @return bool
     */
    public function has(string|array $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();
        $all = $this->all();
        foreach ($keys as $k) {
            if (!array_key_exists($k, $all)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if an input item is present and not empty.
     *
     * @param string|array $key
     * @return bool
     */
    public function filled(string|array $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();
        foreach ($keys as $k) {
            $value = $this->input($k);
            if ($value === null || $value === '') {
                return false;
            }
        }
        return true;
    }

    // Add methods for headers, files, etc. as needed
    public function header(string $key, mixed $default = null): mixed
    {
        $headers = getallheaders(); // Note: May not work on all server setups (e.g., Nginx needs config)
        $key = ucwords(strtolower(str_replace('-', '_', $key)), '_');
        $serverKey = 'HTTP_' . $key;
        return $headers[$key] ?? $_SERVER[$serverKey] ?? $default;
    }


    /**
     * Get a request header.
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    // public function header(string $key, ?string $default = null): ?string {
    //     return $this->symfonyRequest->headers->get($key, $default);
    // }

    public static function createFromGlobals(): self
    {
        // Handle JSON payload automatically if Content-Type is application/json
        SymfonyRequest::enableHttpMethodParameterOverride(); // Allows _method input
        $symfonyRequest = SymfonyRequest::createFromGlobals();

        if (
            str_contains($symfonyRequest->headers->get('CONTENT_TYPE', ''), 'application/json')
            && $symfonyRequest->getContent() !== ''
        ) {
            $data = json_decode($symfonyRequest->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $symfonyRequest->request->replace($data);
            }
        }

        return new static($symfonyRequest);
    }

    /**
     * Get a cookie value from the request.
     * Decrypts automatically if encryption is enabled.
     *
     * @param string $key The cookie name.
     * @param mixed $default Default value if cookie not found or decryption fails.
     * @return mixed
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        $rawValue = $this->symfonyRequest->cookies->get($key);

        if ($rawValue === null) {
            return $default;
        }

        // Attempt decryption via CookieJar if encryption is on
        try {
            /** @var CookieJar $cookieJar */
            $cookieJar = app(CookieJar::class);
            $decryptedValue = $cookieJar->decrypt($rawValue);
            // If decryption returns null (failed), return the default
            return $decryptedValue ?? $default;
        } catch (\Throwable $e) {
            // CookieJar not bound, or decryption failed badly
            // If encryption is off, decrypt() just returns the raw value
            // If it's on and fails, we return default.
            return $default;
        }
    }

    /**
     * Get the bearer token from the Authorization header.
     *
     * @return string|null
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization', '');
        $position = strripos($header, 'Bearer ');

        if ($position !== false) {
            $header = substr($header, $position + 7);
            // Handle cases like "Bearer token, other"
            return str_contains($header, ',') ? strstr($header, ',', true) : $header;
        }
        return null;
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return str_contains($this->header('CONTENT_TYPE', ''), '/json') ||
            str_contains($this->header('CONTENT_TYPE', ''), '+json');
    }

    /**
     * Determine if the current request probably expects a JSON response.
     *
     * @return bool
     */
    public function wantsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, '/json') || str_contains($accept, '+json');
    }

    /**
     * Determine if the current request is an AJAX request.
     * Checks for X-Requested-With header.
     *
     * @return bool
     */
    public function ajax(): bool
    {
        return 'XMLHttpRequest' === $this->header('X-Requested-With');
    }

    /**
     * Get an uploaded file from the request.
     *
     * @param string $key The input name used in the form.
     * @return UploadedFile|null
     */
    public function file(string $key): ?UploadedFile
    {
        return $this->symfonyRequest->files->get($key);
    }

    /**
     * Get all uploaded files from the request.
     *
     * @return array<string, UploadedFile>
     */
    public function allFiles(): array
    {
        return $this->symfonyRequest->files->all();
    }

    /**
     * Get the Session instance. Requires Session setup.
     *
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|null
     */
    public function session(): ?\Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        try {
            // Assuming SessionInterface is bound in the container
            return app(\Symfony\Component\HttpFoundation\Session\SessionInterface::class);
        } catch (\Throwable $e) {
            return null; // Session not available
        }
    }

    // --- Validation ---
    // Basic integration point, needs a Validator class/service

    /**
     * Set the validated data on the request instance.
     * Called by the Validator after successful validation.
     *
     * @param array $data
     * @return $this
     */
    public function setValidated(array $data): static
    {
        $this->validatedData = $data;
        return $this;
    }

    /**
     * Get the validated input data for the request.
     * Throws an exception if validation hasn't occurred or failed.
     *
     * @param string|null $key Specific key or null for all validated data.
     * @param mixed $default Default value if key not in validated data.
     * @return mixed
     * @throws \LogicException
     */
    public function validated(?string $key = null, mixed $default = null): mixed
    {
        if ($this->validatedData === null) {
            throw new \LogicException('Validation has not been performed on this request or it failed.');
        }
        if ($key === null) {
            return $this->validatedData;
        }
        return $this->validatedData[$key] ?? $default;
    }


    /**
     * Dynamically access properties on the underlying Symfony request.
     * e.g., $request->query->get('name')
     */
    public function __get(string $key): mixed
    {
        return $this->symfonyRequest->$key;
    }
}
