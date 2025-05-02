<?php

namespace App\Http\Resources;

use Core\Http\Request;
use Core\Http\Resources\JsonResource;

class UserResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     * @var string|null
     */
    // public static ?string $wrap = 'user'; // Customize wrapping key

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        // $this->resource refers to the User model instance passed in
        return [
            'id' => $this->resource->id, // Access model properties
            'name' => $this->resource->name,
            'email_address' => $this->resource->email, // Rename field
            'created_at' => $this->resource->created_at->toIso8601String(), // Assuming timestamps are Carbon/DateTime objects
            'updated_at' => $this->resource->updated_at->toIso8601String(),

            // Example conditional attribute:
            // 'secret_info' => $this->when($request->user()?->isAdmin(), 'some-secret'),

            // Example relationship (needs model relations defined):
            // 'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}
