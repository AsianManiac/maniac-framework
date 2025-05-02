<?php

namespace Core\Http\Response;

use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Represents an HTTP response for redirection with session flashing capabilities.
 * Extends Symfony's RedirectResponse to allow potential framework-specific customizations.
 */
class RedirectResponse extends SymfonyRedirectResponse
{

    protected ?SessionInterface $session = null;

    /**
     * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
     * Ensures URL generation if a relative path is given.
     *
     * @param string $url The URL to redirect to
     * @param int $status The redirect status code (302 by default)
     * @param array<string, string> $headers The headers (Location is generated automatically)
     */
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        // Basic URL validation/generation (similar to previous basic RedirectResponse)
        if (filter_var($url, FILTER_VALIDATE_URL) === false && !str_starts_with($url, '/')) {
            // Use url() helper if available
            if (function_exists('url')) {
                $url = url($url);
            } else {
                // Fallback: Assume relative to root
                $url = '/' . ltrim($url, '/');
                // Consider logging a warning if url() helper is missing
            }
        }

        parent::__construct($url, $status, $headers);
    }

    /**
     * Sets the session instance. Should be called by the factory or helper.
     *
     * @param SessionInterface $session
     * @return $this
     */
    public function setSession(SessionInterface $session): static
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Flash a key/value pair to the session.
     *
     * @param string $key
     * @param mixed $value
     * @return $this Allows chaining ->with()->with()
     */
    public function with(string $key, mixed $value): static
    {
        if (!$this->session) {
            // Log warning or throw exception if session not available
            trigger_error('Attempted to flash data without a session available.', E_USER_WARNING);
            return $this;
        }
        $this->session->getFlashBag()->add($key, $value);
        return $this;
    }

    /**
     * Flash the input from the current request to the session.
     * Flashes all input by default.
     *
     * @param array|null $input Specific keys to flash, or null for all.
     * @return $this
     */
    public function withInput(?array $input = null): static
    {
        if (!$this->session) return $this; // No session, do nothing

        try {
            // Get input from the current request instance
            $request = app(\Core\Http\Request::class); // Resolve current request
            $flashData = $input === null ? $request->all() : $request->only($input);

            // Remove password fields for security
            unset($flashData['password'], $flashData['password_confirmation']);

            $this->with('_old_input', $flashData); // Flash under a specific key
        } catch (\Throwable $e) {
            // Log warning if request couldn't be resolved
            trigger_error('Could not flash input: Request service unavailable.', E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Flash an array of errors to the session.
     * Typically used with a Validation Errors object/array.
     *
     * @param array $errors Array of error messages, usually keyed by field name.
     * @return $this
     */
    public function withErrors(array $errors): static
    {
        if (!$this->session) return $this; // No session
        // You might want a dedicated ErrorBag class, but simple array for now
        $this->with('_errors', $errors);
        return $this;
    }

    // Override send() or add logic elsewhere to save the session *before* sending headers
    // Symfony's session usually saves automatically on script shutdown via register_shutdown_function
    // but explicitly saving before redirect ensures data is there.
    // public function send(): static {
    //     if ($this->session && $this->session->isStarted()) {
    //         $this->session->save();
    //     }
    //     parent::send();
    //     return $this;
    // }
}
