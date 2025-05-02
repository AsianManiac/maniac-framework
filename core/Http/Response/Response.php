<?php

namespace Core\Http\Response;

use Core\Foundation\Facade;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;


/**
 * Provides static access to the ResponseFactory service.
 *
 * @method static SymfonyResponse make(mixed $content = '', int $status = 200, array $headers = [])
 * @method static JsonResponse json(mixed $data = [], int $status = 200, array $headers = [], int $options = 0)
 * @method static RedirectResponse redirect(string $url, int $status = 302, array $headers = [])
 * @method static SymfonyResponse view(string $viewName, array $data = [], int $status = 200, array $headers = [])
 * @method static SymfonyStreamedResponse stream(callable $callback, int $status = 200, array $headers = [])
 * @method static SymfonyBinaryFileResponse download(string|\SplFileInfo $file, ?string $name = null, array $headers = [], ?string $disposition = 'attachment')
 * @method static SymfonyBinaryFileResponse file(string|\SplFileInfo $file, array $headers = [])
 *
 * @see \Core\Http\Response\ResponseFactory
 * @see \Core\Http\Response\ResponseFactoryInterface
 */
class Response extends Facade
{ // Extends a generic Facade base class
    /**
     * Get the registered name of the component in the container.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        // This key MUST match the key used to bind the ResponseFactory in App::bind()
        return ResponseFactoryInterface::class;
    }
}
