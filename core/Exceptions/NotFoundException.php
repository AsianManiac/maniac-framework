<?php

namespace Core\Exceptions;

/**
 * Thrown when a resource (route, model) is not found.
 * Typically results in a 404 HTTP response.
 */
class NotFoundException extends ManiacException
{
    protected $message = 'Resource not found.';
    protected $code = 404; // Default HTTP status code

    // Optional: Method to set the resource type/ID for better messages
    public function setResource(string $type, mixed $id = null): self
    {
        $this->message = trim("{$type} " . ($id ? "[{$id}] " : '') . "not found.");
        return $this;
    }
}
