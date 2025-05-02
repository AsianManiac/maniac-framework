<?php

namespace Core\Http\Resources;

use Core\Http\Request;
use Core\Support\Collection;

class AnonymousResourceCollection extends JsonResource
{
    /**
     * The name of the resource class being collected.
     * @var string
     */
    public string $collects;

    /**
     * Create a new anonymous resource collection.
     *
     * @param mixed $resource The collection of resources.
     * @param string $collects The resource class name.
     */
    public function __construct(mixed $resource, string $collects)
    {
        $this->collects = $collects;
        // Ensure the resource is a Collection instance
        parent::__construct($this->prepareResource($resource));
    }

    /**
     * Prepare the resource for the collection.
     */
    protected function prepareResource(mixed $resource): Collection
    {
        // Convert pagination results or arrays to a Collection
        if (is_array($resource) && isset($resource['data']) && isset($resource['current_page'])) {
            // Handle pagination structure (assuming structure from Model::paginate)
            $this->with = array_merge($this->with, [
                'meta' => [ // Example meta structure
                    'total' => $resource['total'],
                    'per_page' => $resource['per_page'],
                    'current_page' => $resource['current_page'],
                    'last_page' => $resource['last_page'],
                ]
            ]);
            return collect($resource['data']);
        }

        return collect($resource); // Use our collect() helper or Collection::make()
    }

    /**
     * Transform the resource collection into an array.
     * Maps each item in the underlying collection using the $collects class.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return $this->resource // $this->resource is now a Collection
            ->map(function ($resource) use ($request) {
                // Instantiate the specific resource class for each item
                /** @var JsonResource $resourceClass */
                $resourceClass = $this->collects;
                $itemResource = new $resourceClass($resource);
                return $itemResource->resolve($request); // Resolve individual item
            })
            ->all(); // Get the final array from the mapped collection
    }

    /**
     * Get additional data that should be returned with the resource array.
     * Overrides parent to ensure 'with' data from collection is included.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        // 'with' data might have been added during pagination handling
        return $this->with;
    }
}
