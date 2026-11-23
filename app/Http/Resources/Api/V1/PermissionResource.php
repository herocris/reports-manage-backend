<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Permission model into the API JSON shape.
 *
 * Exposes `roles` (array of role name strings) when loaded.
 */
class PermissionResource extends JsonResource
{
    /**
     * Transform the Permission model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed> Shape: {id, name?, roles?: string[], created_at?, updated_at?}
     *                              Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->whenHas('name', fn () => (string) $this->name),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
        ];
    }
}
