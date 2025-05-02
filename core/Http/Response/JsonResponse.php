<?php

namespace Core\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

/**
 * Represents an HTTP response delivering JSON data.
 * Extends Symfony's JsonResponse to allow potential framework-specific customizations.
 */
class JsonResponse extends SymfonyJsonResponse
{
    /**
     * Constructor.
     * Adds default JSON options like JSON_PRETTY_PRINT in debug mode.
     *
     * @param mixed $data The response data
     * @param int $status The response status code
     * @param array $headers An array of response headers
     * @param bool $json If the data is already JSON encoded
     */
    public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        // Default options - add pretty print in debug mode for easier reading
        $encodingOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if (config('app.debug', false)) { // Use config helper safely
            $encodingOptions |= JSON_PRETTY_PRINT;
        }

        parent::__construct($data, $status, $headers, $json);
        $this->setEncodingOptions($encodingOptions); // Set our default options
    }

    // You can add more methods here specific to your framework's JSON responses if needed.
}
