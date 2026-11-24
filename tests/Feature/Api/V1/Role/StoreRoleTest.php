<?php

namespace Tests\Feature\Api\V1\Role;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new role records.
 */
class StoreRoleTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    private const GUARD_NAME = 'sanctum';

    public function test_authenticated_user_can_create_a_role_with_permissions(): void
    {
        $actor = $this->createAuthorizedUser(['roles.create']);
        Permission::create(['name' => 'users.view', 'guard_name' => self::GUARD_NAME]);
        Permission::create(['name' => 'users.create', 'guard_name' => self::GUARD_NAME]);
        $actor->givePermissionTo(['users.view', 'users.create']);

        $response = $this->actingAs($actor)->postJson('/api/v1/roles', [
            'name' => 'admin',
            'permissions' => ['users.view', 'users.create'],
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'admin']);

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseCount('role_has_permissions', 2);
    }

    public function test_store_validates_required_and_unique_name(): void
    {
        $actor = $this->createAuthorizedUser(['roles.create']);
        Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);

        $missingNameResponse = $this->actingAs($actor)->postJson('/api/v1/roles', []);
        $duplicateNameResponse = $this->actingAs($actor)->postJson('/api/v1/roles', [
            'name' => 'admin',
        ]);

        $missingNameResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $duplicateNameResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_permissions_exist(): void
    {
        $actor = $this->createAuthorizedUser(['roles.create']);

        $response = $this->actingAs($actor)->postJson('/api/v1/roles', [
            'name' => 'admin',
            'permissions' => ['missing.permission'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['permissions.0']);
    }
}
