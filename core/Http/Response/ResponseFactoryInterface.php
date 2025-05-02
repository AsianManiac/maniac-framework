<?php

namespace Core\Http\Response;

use SplFileInfo;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;

/**
 * Defines the contract for a factory that creates various HTTP response instances.
 */
interface ResponseFactoryInterface
{
    /**
     * Create a new response instance.
     *
     * @param string|mixed $content The response content.
     * @param int $status The HTTP status code.
     * @param array $headers An array of response headers.
     * @return SymfonyResponse
     */
    public function make(mixed $content = '', int $status = 200, array $headers = []): SymfonyResponse;

    /**
     * Create a new JSON response instance.
     *
     * @param mixed $data The data to be JSON encoded.
     * @param int $status The HTTP status code.
     * @param array $headers An array of response headers.
     * @param int $options JSON encoding options.
     * @return JsonResponse Should return our custom JsonResponse extending Symfony's
     */
    public function json(mixed $data = [], int $status = 200, array $headers = [], int $options = 0): JsonResponse;

    /**
     * Create a new redirect response instance.
     *
     * @param string $url The URL to redirect to.
     * @param int $status The HTTP status code (usually 301 or 302).
     * @param array $headers An array of response headers.
     * @return RedirectResponse Should return our custom RedirectResponse extending Symfony's
     */
    public function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse;

    /**
     * Create a new view response instance.
     * Renders the view and wraps it in a Response object.
     *
     * @param string $viewName The name of the view (dot notation).
     * @param array $data Data to pass to the view.
     * @param int $status The HTTP status code.
     * @param array $headers An array of response headers.
     * @return SymfonyResponse
     */
    public function view(string $viewName, array $data = [], int $status = 200, array $headers = []): SymfonyResponse;

    /**
     * Create a new streamed response instance.
     *
     * @param callable $callback The callback that will stream the response content.
     * @param int $status The HTTP status code.
     * @param array $headers An array of response headers.
     * @return SymfonyStreamedResponse
     */
    public function stream(callable $callback, int $status = 200, array $headers = []): SymfonyStreamedResponse;

    /**
     * Create a new file download response instance.
     *
     * @param string|SplFileInfo $file The file path or SplFileInfo object.
     * @param string|null $name The name the user should see (optional, deduced otherwise).
     * @param array $headers Additional headers.
     * @param string|null $disposition Content disposition ('attachment' or 'inline', defaults to 'attachment').
     * @return SymfonyBinaryFileResponse
     */
    public function download(string|SplFileInfo $file, ?string $name = null, array $headers = [], ?string $disposition = 'attachment'): SymfonyBinaryFileResponse;

    /**
     * Create a new response for serving a file directly.
     *
     * @param string|SplFileInfo $file The file path or SplFileInfo object.
     * @param array $headers Additional headers.
     * @return SymfonyBinaryFileResponse
     */
    public function file(string|SplFileInfo $file, array $headers = []): SymfonyBinaryFileResponse;

    /**
     * Add a header to be applied to the next created response.
     * (Note: This adds complexity, maybe better to pass headers directly).
     * Consider if this fluent header setting is truly needed on the factory.
     *
     * @param string $key
     * @param string|array $values
     * @param bool $replace
     * @return $this
     */
    // public function header(string $key, $values, bool $replace = true): static;

    /**
     * Add a cookie to be applied to the next created response.
     * (Note: Similar complexity concerns as header()).
     *
     * @param \Symfony\Component\HttpFoundation\Cookie|string $cookie
     * @param string|null $value
     * @return $this
     */
    // public function cookie(/* ... */): static;

}
