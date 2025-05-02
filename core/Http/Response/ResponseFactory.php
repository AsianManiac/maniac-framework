<?php

namespace Core\Http\Response;

use SplFileInfo;
use Core\View\ViewEngineInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;

class ResponseFactory implements ResponseFactoryInterface
{
    protected ViewEngineInterface $viewEngine;

    /**
     * Constructor.
     *
     * @param ViewEngineInterface $viewEngine Instance of the view rendering engine. // <<< Update DocBlock
     */
    public function __construct(ViewEngineInterface $viewEngine)
    {
        $this->viewEngine = $viewEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function make(mixed $content = '', int $status = 200, array $headers = []): SymfonyResponse
    {
        return new SymfonyResponse($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function json(mixed $data = [], int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        // Use our custom JsonResponse which handles default options
        $response = new JsonResponse($data, $status, $headers);
        if ($options !== 0) { // Allow overriding default options
            $response->setEncodingOptions($options);
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        $redirect = new RedirectResponse($url, $status, $headers);
        // Try to get session and set it on the redirect
        try {
            $session = app(SessionInterface::class);
            $redirect->setSession($session);
        } catch (\Throwable $e) {
            // Session not available, flashing won't work. Log maybe?
        }
        return $redirect;
    }

    /**
     * {@inheritdoc}
     */
    public function view(string $viewName, array $data = [], int $status = 200, array $headers = []): SymfonyResponse
    {
        // Render the view using the injected ViewEngine
        $content = $this->viewEngine->render($viewName, $data);

        // Set default Content-Type header if not provided
        if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
            $headers['Content-Type'] = 'text/html; charset=UTF-8';
        }

        return $this->make($content, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function stream(callable $callback, int $status = 200, array $headers = []): SymfonyStreamedResponse
    {
        return new SymfonyStreamedResponse($callback, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function download(string|SplFileInfo $file, ?string $name = null, array $headers = [], ?string $disposition = 'attachment'): SymfonyBinaryFileResponse
    {
        $response = new SymfonyBinaryFileResponse($file, 200, $headers, true, $disposition); // true = public

        if ($name !== null) {
            return $response->setContentDisposition($disposition ?? 'attachment', $name);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function file(string|SplFileInfo $file, array $headers = []): SymfonyBinaryFileResponse
    {
        return new SymfonyBinaryFileResponse($file, 200, $headers);
    }

    // --- Add helper methods for common headers if desired ---
    // Example: ->withHeaders() ->withCookie() etc. might belong on the
    // Response objects themselves rather than the factory.
    // Keep the factory focused on *creating* different response *types*.
}
