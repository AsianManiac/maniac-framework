<?php

namespace Core\Hashing;

use Core\Foundation\Facade;

/**
 * @method static string make(string $value)
 * @method static bool check(string $value, string $hashedValue)
 * @method static bool needsRehash(string $hashedValue)
 *
 * @see \Core\Hashing\Hasher
 */
class Hash extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return Hasher::class; // Bind Hasher in App container
    }
}
