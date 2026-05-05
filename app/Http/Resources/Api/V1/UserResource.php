<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a User model into the API JSON shape.
 *
 * `email_verified_at` is only exposed when the requesting user is viewing
 * their own profile (`$request->user()->id === $this->id`). All other users
 * see this field omitted, regardless of their roles or permissions.
 *
 * `roles` and `permissions` are only present when the respective relations
 * have been eager-loaded by the calling controller.
 */
class UserResource extends JsonResource
{
    /**
     * Transform the User model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request (used for owner-based field visibility).
     * @return array<string, mixed>  Shape: {id, name?, email?, email_verified_at? (owner only),
     *   institucion_id?, roles?: string[], permissions?: string[],
     *   institucion?: InstitucionResource, created_at?, updated_at?}
     *   Fields marked with `?` are omitted when not loaded or not authorized.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->whenHas('name', fn () => (string) $this->name),
            'email' => $this->whenHas('email', fn () => (string) $this->email),
            'email_verified_at' => $this->when(
                $request->user()?->id === $this->id,
                fn () => $this->email_verified_at,
            ),
            'institucion_id' => $this->whenHas('institucion_id', fn () => (int) $this->institucion_id),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')),
            'institucion' => new InstitucionResource($this->whenLoaded('institucion')),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
        ];
    }
}
