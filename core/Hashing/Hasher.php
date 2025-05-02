<?php

namespace Core\Hashing;

use RuntimeException;

class Hasher
{

    /**
     * Default hashing algorithm.
     * PASSWORD_BCRYPT is the current PHP default and strong.
     * PASSWORD_ARGON2ID is stronger but requires libsodium.
     */
    protected string $algo = PASSWORD_DEFAULT; // Use PHP's default (usually bcrypt)
    protected array $options = []; // Options for the algorithm (e.g., cost for bcrypt)

    /**
     * Hash the given value.
     *
     * @param string $value The value to hash.
     * @return string The hashed value.
     * @throws RuntimeException If hashing fails.
     */
    public function make(string $value): string
    {
        $hash = password_hash($value, $this->algo, $this->options);

        if ($hash === false) {
            throw new RuntimeException('Password hashing failed.');
        }
        return $hash;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param string $value The plain value.
     * @param string $hashedValue The previously hashed value.
     * @return bool True if the value matches the hash.
     */
    public function check(string $value, string $hashedValue): bool
    {
        if (strlen($hashedValue) === 0) {
            return false; // Cannot check against an empty hash
        }
        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the given hash has been hashed using the configured options.
     * Useful for rehashing if options change (e.g., bcrypt cost).
     *
     * @param string $hashedValue
     * @return bool
     */
    public function needsRehash(string $hashedValue): bool
    {
        return password_needs_rehash($hashedValue, $this->algo, $this->options);
    }
}
