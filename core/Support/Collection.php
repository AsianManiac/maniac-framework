<?php

namespace Core\Support;

use Closure;
use Countable;
use Exception;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;

/**
 * A fluent, array-like object for working with arrays of data.
 * Inspired by Laravel's Illuminate\Support\Collection.
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * The items contained in the collection.
     * @var array
     */
    protected array $items = [];

    /**
     * Create a new collection.
     *
     * @param mixed $items Items to populate the collection.
     */
    public function __construct(mixed $items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Create a new collection instance.
     * Static factory method.
     *
     * @param mixed $items
     * @return static
     */
    public static function make(mixed $items = []): static
    {
        return new static($items);
    }

    /**
     * Get all items in the collection.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get the average value of a given key or the items themselves.
     *
     * @param string|null $key
     * @return float|int|null
     */
    public function avg(string|callable|null $key = null): float|int|null
    {
        $values = $this->pluck($key)->filter(fn($value) => !is_null($value));
        if ($values->isEmpty()) return null;
        return array_sum($values->all()) / $values->count();
    }

    /**
     * Get the sum of the given values.
     *
     * @param string|callable|null $key
     * @return float|int
     */
    public function sum(string|callable|null $key = null): float|int
    {
        if (is_null($key)) {
            return array_sum($this->items);
        }
        return $this->pluck($key)->sum();
    }


    /**
     * Run a map over each of the items.
     *
     * @param callable $callback
     * @return static A new collection with the mapped items.
     */
    public function map(callable $callback): static
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);
        return new static(array_combine($keys, $items));
    }

    /**
     * Filter items by the given callback.
     *
     * @param callable|null $callback If null, filters falsy values.
     * @return static A new filtered collection.
     */
    public function filter(?callable $callback = null): static
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }
        return new static(array_filter($this->items));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param callable $callback
     * @param mixed $initial Initial value for the reduction.
     * @return mixed The reduced value.
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param callable|null $callback
     * @param mixed $default Default value if no item found.
     * @return mixed
     */
    public function first(?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $default;
            }
            return reset($this->items); // Get first element
        }
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Get the last item from the collection passing the given truth test.
     *
     * @param callable|null $callback
     * @param mixed $default Default value if no item found.
     * @return mixed
     */
    public function last(?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $default;
            }
            return end($this->items); // Get last element
        }
        // Iterate in reverse for efficiency if possible, or just iterate normally
        $found = $default;
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                $found = $value; // Keep updating until the end
            }
        }
        return $found;
    }


    /**
     * Get the value for a given key.
     *
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string|int $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * Pluck an array of values from the collection.
     *
     * @param string|callable|null $valueKey The key to pluck, or a callback. Null plucks the item itself.
     * @param string|null $indexKey Optional key to use for the resulting array's keys.
     * @return static A new collection of plucked values.
     */
    public function pluck(string|callable|null $valueKey, ?string $indexKey = null): static
    {
        $results = [];
        $valueRetriever = is_callable($valueKey) ? $valueKey : (is_null($valueKey) ? fn($item) => $item : fn($item) => data_get($item, $valueKey));
        $indexRetriever = is_null($indexKey) ? fn($item, $key) => $key : fn($item) => data_get($item, $indexKey);

        foreach ($this->items as $key => $item) {
            $itemValue = $valueRetriever($item, $key);
            $itemKey = $indexRetriever($item, $key);
            if ($itemKey !== null) {
                $results[$itemKey] = $itemValue;
            } else {
                $results[] = $itemValue; // Append if no index key
            }
        }
        return new static($results);
    }

    /**
     * Filter items based on a key/value pair.
     *
     * @param string $key
     * @param mixed $operator Can be an operator (=, !=, >, <, etc.) or the value (implies '=')
     * @param mixed $value Value to compare against (if operator is provided)
     * @return static
     */
    public function where(string $key, mixed $operator, mixed $value = null): static
    {
        // Handle where('key', 'value') shorthand
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->filter(function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key); // Helper to get nested data potentially

            return match (strtolower($operator)) {
                '=', '==' => $retrieved == $value,
                '===', 'strict' => $retrieved === $value,
                '!=', '<>' => $retrieved != $value,
                '!==' => $retrieved !== $value,
                '<' => $retrieved < $value,
                '<=' => $retrieved <= $value,
                '>' => $retrieved > $value,
                '>=' => $retrieved >= $value,
                'like' => str_contains(strtolower((string) $retrieved), strtolower((string) $value)), // Basic like
                'in' => is_array($value) && in_array($retrieved, $value),
                'notin' => is_array($value) && !in_array($retrieved, $value),
                default => false, // Unknown operator
            };
        });
    }

    /**
     * Determine if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Convert the collection to its array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof self ? $value->toArray() : $value; // Recursively convert nested collections
        }, $this->items);
    }

    /**
     * Convert the collection to its JSON representation.
     *
     * @param int $options JSON encoding options.
     * @return string|false JSON string or false on failure.
     */
    public function toJson(int $options = 0): string|false
    {
        try {
            return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            // Handle or log JSON encoding error
            trigger_error("Could not encode collection to JSON: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    // --- Interface Implementations ---

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value; // Append
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function jsonSerialize(): array
    {
        // Prepare items for JSON serialization (e.g., convert nested objects)
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof self) { // Handle nested collections
                return $value->jsonSerialize();
            } elseif (is_object($value) && method_exists($value, 'toArray')) { // Check for toArray method
                return $value->toArray();
            }
            // Add other object-to-array conversions if needed
            return $value;
        }, $this->items);
    }

    // --- Helper Methods ---

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param mixed $items
     * @return array
     */
    protected function getArrayableItems(mixed $items): array
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof \JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof \Traversable) {
            return iterator_to_array($items);
        } elseif (is_object($items) && method_exists($items, 'toArray')) {
            return $items->toArray();
        }
        // Handle single item passed
        return (array) $items;
    }

    // Add many more methods: each, contains, unique, sort, groupBy, keyBy, prepend, push, pop, shift, etc.
}
