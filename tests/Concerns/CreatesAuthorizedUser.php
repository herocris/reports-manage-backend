<?php

namespace Tests\Concerns;

use App\Models\Permission;
use App\Models\User;

/**
 * Helper trait for creating authenticated users with Sanctum tokens and Spatie permissions.
 *
 * Used across all feature tests to quickly set up an acting user
 * without repeating auth boilerplate.
 */
trait CreatesAuthorizedUser
{
    private function createAuthorizedUser(array $permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'sanctum');
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}
