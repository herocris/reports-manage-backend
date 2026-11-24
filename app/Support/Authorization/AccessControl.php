<?php

namespace App\Support\Authorization;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccessControl
{
    /** @param list<string> $permissions */
    public function authorizePermissions(User $actor, array $permissions): void
    {
        if (array_diff($permissions, $actor->getAllPermissions()->pluck('name')->all()) !== []) {
            throw new AuthorizationException('You cannot manage permissions you do not possess.');
        }
    }

    public function authorizeUser(User $actor, User $target): void
    {
        $permissions = $target->permissions()->withTrashed()->pluck('name');
        $roles = $target->roles()->withTrashed()->with([
            'permissions' => function (BelongsToMany $query): void {
                $query->withTrashed();
            },
        ])->get();

        $this->authorizePermissions($actor, $permissions
            ->merge($roles->flatMap(fn (Role $role) => $role->permissions->pluck('name')))
            ->unique()
            ->values()
            ->all());
    }

    public function authorizeRole(User $actor, Role $role): void
    {
        $this->authorizePermissions($actor, $role->permissions()->withTrashed()->pluck('name')->all());
    }

    public function authorizePermissionTargets(User $actor, Permission $permission): void
    {
        foreach ($permission->roles()->withTrashed()->get() as $role) {
            $this->authorizeRole($actor, $role);
        }

        foreach ($permission->users()->withTrashed()->get() as $user) {
            $this->authorizeUser($actor, $user);
        }
    }

    /** @param list<string> $roles */
    public function authorizeRoles(User $actor, array $roles): void
    {
        foreach (Role::query()->where('guard_name', 'sanctum')->whereIn('name', $roles)->get() as $role) {
            $this->authorizeRole($actor, $role);
        }
    }

    /** @param list<string> $roles */
    public function authorizeRoleAssignment(User $actor, array $roles): void
    {
        if (! $actor->can('users.assign-roles')) {
            throw new AuthorizationException('Role assignment requires a separate permission.');
        }

        $this->authorizeRoles($actor, $roles);
    }
}
