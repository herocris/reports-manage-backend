<?php

namespace Tests\Feature\Api\V1\Permission;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting permission records.
 */
class ShowUpdateDestroyPermissionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_a_permission(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.view']);
        $permission = Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);

        $this->actingAs($actor)->getJson("/api/v1/permissions/{$permission->id}")
            ->assertOk()
            ->assertJsonFragment(['name' => 'reports.export'])
            ->assertJsonMissingPath('data.users_count');
    }

    public function test_authenticated_user_can_update_a_permission_and_sync_roles(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.update']);
        $permission = Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);

        $response = $this->actingAs($actor)->putJson("/api/v1/permissions/{$permission->id}", [
            'name' => 'reports.download',
            'roles' => ['admin'],
        ]);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'reports.download'])
            ->assertJsonMissingPath('data.users_count');

        $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'name' => 'reports.download']);
        $this->assertTrue($permission->fresh()->hasRole('admin'));
    }

    public function test_update_validates_unique_name_ignoring_current_permission(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.update']);
        $permission = Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);

        $this->actingAs($actor)->putJson("/api/v1/permissions/{$permission->id}", [
            'name' => 'reports.export',
        ])->assertOk();
    }

    public function test_update_rejects_duplicate_name_from_another_permission(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.update']);
        Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);
        $permission = Permission::create(['name' => 'reports.download', 'guard_name' => 'sanctum']);

        $this->actingAs($actor)->putJson("/api/v1/permissions/{$permission->id}", [
            'name' => 'reports.export',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }

    public function test_authenticated_user_can_delete_a_permission(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.delete']);
        $permission = Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);

        $this->actingAs($actor)->deleteJson("/api/v1/permissions/{$permission->id}")
            ->assertOk()
            ->assertJson(['message' => 'Permission deleted successfully.']);

        $this->assertSoftDeleted($permission);
    }

    public function test_unauthenticated_user_cannot_manage_permissions(): void
    {
        $permission = Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);

        $this->getJson('/api/v1/permissions')->assertUnauthorized();
        $this->postJson('/api/v1/permissions', ['name' => 'reports.download'])->assertUnauthorized();
        $this->getJson("/api/v1/permissions/{$permission->id}")->assertUnauthorized();
        $this->putJson("/api/v1/permissions/{$permission->id}", ['name' => 'reports.download'])->assertUnauthorized();
        $this->deleteJson("/api/v1/permissions/{$permission->id}")->assertUnauthorized();
    }

    public function test_authenticated_user_without_permission_cannot_manage_permissions(): void
    {
        $actor = User::factory()->create();
        $permission = Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);

        $this->actingAs($actor)->getJson('/api/v1/permissions')->assertForbidden();
        $this->actingAs($actor)->postJson('/api/v1/permissions', ['name' => 'reports.download'])->assertForbidden();
        $this->actingAs($actor)->getJson("/api/v1/permissions/{$permission->id}")->assertForbidden();
        $this->actingAs($actor)->putJson("/api/v1/permissions/{$permission->id}", ['name' => 'reports.download'])->assertForbidden();
        $this->actingAs($actor)->deleteJson("/api/v1/permissions/{$permission->id}")->assertForbidden();
    }

    public function test_show_returns_404_for_nonexistent_permission(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.view']);

        $this->actingAs($actor)->getJson('/api/v1/permissions/99999')->assertNotFound();
    }
}
