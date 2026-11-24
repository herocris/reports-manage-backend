<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Role model into the API JSON shape.
 *
 * Exposes `permissions` (array of permission name strings) when loaded,
 * and `users_count` when the count aggregate is available.
 */
class RoleResource extends JsonResource
{
    /**
     * Transform the Role model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed> Shape: {id, name?, permissions?: string[],
     *                              users_count?: int, created_at?, updated_at?}
     *                              Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->whenHas('name', fn () => (string) $this->name),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')->values()),
            'users_count' => $this->whenCounted('users'),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
        ];
    }
}
