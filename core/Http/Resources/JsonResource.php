<?php

namespace Core\Http\Resources;

use JsonSerializable;
use Core\Http\Request;
use Core\Support\Collection;

/**
 * Base class for representing models and collections in API responses.
 */
abstract class JsonResource implements JsonSerializable
{
    /**
     * The underlying model resource instance.
     * @var mixed
     */
    protected mixed $resource;

    /**
     * Additional data that should be added to the resource response.
     * @var array
     */
    public array $with = [];

    /**
     * The "data" wrapper that should be applied.
     * @var string|null
     */
    public static ?string $wrap = 'data'; // Default wrap key

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource The underlying data (model, collection, array).
     */
    public function __construct(mixed $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Create a new anonymous resource collection.
     *
     * @param mixed $resource
     * @return AnonymousResourceCollection
     */
    public static function collection(mixed $resource): AnonymousResourceCollection
    {
        return new AnonymousResourceCollection($resource, static::class);
    }

    /**
     * Transform the resource into an array.
     * This method MUST be implemented by concrete resource classes.
     *
     * @param Request $request
     * @return array The array representation of the resource.
     */
    abstract public function toArray(Request $request): array;

    /**
     * Resolve the resource to an array.
     * Handles unwrapping nested resources and filtering nulls.
     *
     * @param Request|null $request
     * @return array
     */
    public function resolve(?Request $request = null): array
    {
        $request = $request ?? app(Request::class); // Get current request

        // Ensure resource is not null before calling toArray
        if (is_null($this->resource)) {
            return [];
        }

        $data = $this->toArray($request);

        // Remove attributes marked for removal when null (needs implementation)
        // $data = $this->filter($data);

        return array_merge($data, $this->with($request));
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return $this->with; // Return pre-defined 'with' data
    }

    /**
     * Specify data that should be available for the request.
     * Fluent method.
     *
     * @param array $with
     * @return $this
     */
    public function additional(array $with): static
    {
        $this->with = array_merge($this->with, $with);
        return $this;
    }

    /**
     * Prepare the resource for JSON serialization.
     * Applies the 'data' wrapping if configured.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $resolvedData = $this->resolve();

        if (static::$wrap && !isset($resolvedData[static::$wrap]) && !array_key_exists(static::$wrap, $resolvedData)) {
            // Apply wrapping if wrap key is set and not already present
            return [
                static::$wrap => $resolvedData,
                // Merge 'with' data at the top level alongside the wrap key
                ...$this->with(app(Request::class))
            ];
        } elseif (is_null(static::$wrap)) {
            // No wrapping, merge 'with' data directly
            return array_merge($resolvedData, $this->with(app(Request::class)));
        } else {
            // Wrapping key already exists in resolved data or wrapping is disabled
            // Merge 'with' data at the top level
            return array_merge($resolvedData, $this->with(app(Request::class)));
        }
    }

    /**
     * Create a new resource instance when the given condition is true.
     *
     * @param bool $condition
     * @param mixed ...$parameters Parameters for the resource constructor.
     * @return static|null Returns instance or null if condition is false.
     */
    public static function makeWhen(bool $condition, ...$parameters): ?static
    {
        return $condition ? new static(...$parameters) : null;
    }

    // Add methods for conditional attributes: $this->when(), $this->mergeWhen()
    // Add methods for relationship loading: $this->whenLoaded()
}
