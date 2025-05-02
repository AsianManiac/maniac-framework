<?php

use Core\Support\Collection;

if (!function_exists('collect')) {
    /**
     * Create a new collection instance.
     *
     * @param mixed $value
     * @return Collection
     */
    function collect(mixed $value = []): Collection
    {
        return new Collection($value);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     * Basic implementation.
     *
     * @param mixed $target Target array or object.
     * @param string|array|int|null $key Key to retrieve (dot notation for arrays).
     * @param mixed $default Default value.
     * @return mixed
     */
    function data_get(mixed $target, string|array|int|null $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', (string) $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                // Handle wildcard - basic implementation returns array of values
                if (!is_array($target)) return $default;
                $result = [];
                foreach ($target as $item) {
                    // Recursively call data_get with remaining key parts
                    $result[] = data_get($item, $key, $default);
                }
                // If key is empty, return the result array, otherwise it's handled in next loop
                return empty($key) ? $result : $default; // Simplification, deep wildcard needs more logic
            }

            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $default; // Segment not found
            }
        }
        return $target; // Final value
    }
}
