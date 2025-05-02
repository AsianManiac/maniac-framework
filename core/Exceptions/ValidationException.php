<?php

namespace Core\Exceptions;

use Throwable;

/**
 * Thrown when data validation fails.
 * Typically results in a redirect back with errors or a 422 JSON response.
 */
class ValidationException extends ManiacException
{
    protected array $errors;
    protected $code = 422; // Unprocessable Entity

    public function __construct(array $errors, string $message = 'The given data was invalid.', ?Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $this->code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
