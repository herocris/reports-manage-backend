<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

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
     * @return array<string, mixed> Shape: {id, name?, email?, email_verified_at? (owner only),
     *                              institucion_id?, roles?: string[], permissions?: string[],
     *                              institucion?: {id: int, nombre: string}, created_at?, updated_at?}
     *                              Fields marked with `?` are omitted when not loaded or not authorized.
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
            'permissions' => $this->when(
                $this->relationLoaded('roles') || $this->relationLoaded('permissions'),
                fn () => $this->resolvePermissions(),
            ),
            'institucion' => $this->whenLoaded('institucion', fn () => [
                'id' => (int) $this->institucion->id,
                'nombre' => (string) $this->institucion->nombre,
            ]),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
        ];
    }

    /**
     * Resolve user permissions for API output.
     *
     * Returns all system permissions for users with the `super-admin` role.
     * For any other role, returns effective permissions granted via roles and
     * direct user permissions.
     *
     * @return Collection<int, string>
     */
    private function resolvePermissions()
    {
        $hasSuperAdminRole = $this->roles->contains(
            fn ($role): bool => strtolower((string) $role->name) === 'super-admin'
        );

        if ($hasSuperAdminRole) {
            return Permission::query()
                ->where('guard_name', 'sanctum')
                ->pluck('name')
                ->values();
        }

        return $this->getAllPermissions()
            ->pluck('name')
            ->values();
    }
}
