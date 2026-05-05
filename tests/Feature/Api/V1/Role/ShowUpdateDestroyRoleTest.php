<?php

namespace Tests\Feature\Api\V1\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting role records.
 */
class ShowUpdateDestroyRoleTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    private const GUARD_NAME = 'sanctum';

    public function test_authenticated_user_can_view_a_role(): void
    {
        $actor = $this->createAuthorizedUser(['roles.view']);
        $role = Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);

        $response = $this->actingAs($actor)->getJson("/api/v1/roles/{$role->id}");

        $response->assertOk()
            ->assertJsonFragment(['name' => 'admin']);
    }

    public function test_authenticated_user_can_update_a_role_and_sync_permissions(): void
    {
        $actor = $this->createAuthorizedUser(['roles.update']);
        $role = Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);
        Permission::create(['name' => 'roles.view', 'guard_name' => self::GUARD_NAME]);

        $response = $this->actingAs($actor)->putJson("/api/v1/roles/{$role->id}", [
            'name' => 'super-admin',
            'permissions' => ['roles.view'],
        ]);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'super-admin']);

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'super-admin']);
        $this->assertTrue($role->fresh()->hasPermissionTo('roles.view'));
    }

    public function test_update_validates_unique_name_ignoring_current_role(): void
    {
        $actor = $this->createAuthorizedUser(['roles.update']);
        $role = Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);

        $response = $this->actingAs($actor)->putJson("/api/v1/roles/{$role->id}", [
            'name' => 'admin',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_duplicate_name_from_another_role(): void
    {
        $actor = $this->createAuthorizedUser(['roles.update']);
        Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);
        $role = Role::create(['name' => 'manager', 'guard_name' => self::GUARD_NAME]);

        $response = $this->actingAs($actor)->putJson("/api/v1/roles/{$role->id}", [
            'name' => 'admin',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_authenticated_user_can_delete_a_role(): void
    {
        $actor = $this->createAuthorizedUser(['roles.delete']);
        $role = Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);

        $response = $this->actingAs($actor)->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Role deleted successfully.']);

        $this->assertSoftDeleted($role);
    }

    public function test_unauthenticated_user_cannot_manage_roles(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);

        $this->postJson('/api/v1/roles', ['name' => 'manager'])->assertUnauthorized();
        $this->getJson("/api/v1/roles/{$role->id}")->assertUnauthorized();
        $this->putJson("/api/v1/roles/{$role->id}", ['name' => 'manager'])->assertUnauthorized();
        $this->deleteJson("/api/v1/roles/{$role->id}")->assertUnauthorized();
    }

    public function test_show_returns_404_for_nonexistent_role(): void
    {
        $actor = $this->createAuthorizedUser(['roles.view']);

        $response = $this->actingAs($actor)->getJson('/api/v1/roles/99999');

        $response->assertNotFound();
    }

    public function test_authenticated_user_without_permission_cannot_manage_roles(): void
    {
        $actor = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);

        $this->actingAs($actor)->getJson('/api/v1/roles')->assertForbidden();
        $this->actingAs($actor)->postJson('/api/v1/roles', ['name' => 'manager'])->assertForbidden();
        $this->actingAs($actor)->getJson("/api/v1/roles/{$role->id}")->assertForbidden();
        $this->actingAs($actor)->putJson("/api/v1/roles/{$role->id}", ['name' => 'manager'])->assertForbidden();
        $this->actingAs($actor)->deleteJson("/api/v1/roles/{$role->id}")->assertForbidden();
    }
}
