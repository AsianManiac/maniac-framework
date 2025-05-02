<?php

namespace Core\Exceptions;

use Throwable;

/**
 * Represents an exception with an associated HTTP status code.
 */
class HttpException extends ManiacException
{
    protected int $statusCode;
    protected array $headers;

    public function __construct(int $statusCode, string $message = '', ?Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        parent::__construct($message, $code ?? $statusCode, $previous); // Use status as code if code not given
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
