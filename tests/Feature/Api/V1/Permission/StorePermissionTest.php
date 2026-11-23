<?php

namespace Tests\Feature\Api\V1\Permission;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new permission records.
 */
class StorePermissionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_a_permission_with_roles(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.create']);
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);

        $response = $this->actingAs($actor)->postJson('/api/v1/permissions', [
            'name' => 'reports.export',
            'roles' => ['admin'],
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'reports.export'])
            ->assertJsonMissingPath('data.users_count');

        $this->assertDatabaseHas('permissions', ['name' => 'reports.export']);
    }

    public function test_store_validates_required_and_unique_name(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.create']);
        Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);

        $this->actingAs($actor)->postJson('/api/v1/permissions', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->actingAs($actor)->postJson('/api/v1/permissions', ['name' => 'reports.export'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_roles_exist(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.create']);

        $this->actingAs($actor)->postJson('/api/v1/permissions', [
            'name' => 'reports.export',
            'roles' => ['missing-role'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['roles.0']);
    }
}
