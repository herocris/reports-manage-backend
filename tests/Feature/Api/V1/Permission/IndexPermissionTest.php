<?php

namespace Tests\Feature\Api\V1\Permission;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering permission records.
 */
class IndexPermissionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_permissions(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.view']);
        Permission::create(['name' => 'users.view', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'users.create', 'guard_name' => 'sanctum']);

        $response = $this->actingAs($actor)->getJson('/api/v1/permissions');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_index_supports_include_roles(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.view']);
        $role = Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        $permission = Permission::create(['name' => 'reports.view', 'guard_name' => 'sanctum']);
        $permission->assignRole($role);

        $this->actingAs($actor)
            ->getJson('/api/v1/permissions?include=roles&filter[name]=reports.view&per_page=100')
            ->assertOk()
            ->assertJsonPath('data.0.roles.0', 'admin');
    }

    public function test_index_supports_sparse_fields_selection(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.view']);
        Permission::create(['name' => 'users.view', 'guard_name' => 'sanctum']);

        $this->actingAs($actor)
            ->getJson('/api/v1/permissions?fields=id,name&per_page=100')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonMissingPath('data.0.guard_name');
    }

    public function test_index_supports_filter_by_role(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.view']);
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'sanctum']);
        $adminPermission = Permission::create(['name' => 'reports.export', 'guard_name' => 'sanctum']);
        $viewerPermission = Permission::create(['name' => 'reports.view', 'guard_name' => 'sanctum']);
        $adminPermission->assignRole($admin);
        $viewerPermission->assignRole($viewer);

        $response = $this->actingAs($actor)
            ->getJson('/api/v1/permissions?filter[role]=admin&per_page=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'reports.export');
    }

    public function test_index_supports_only_trashed_and_sorting(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.view']);
        Permission::create(['name' => 'zzsort.alpha.active', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'zzsort.zeta.active', 'guard_name' => 'sanctum']);
        $deletedPermission = Permission::create(['name' => 'zzsort.deleted', 'guard_name' => 'sanctum']);
        $deletedPermission->delete();

        $sortedResponse = $this->actingAs($actor)
            ->getJson('/api/v1/permissions?sort=name&direction=asc&per_page=100');

        $sortedResponse->assertOk();
        $names = collect($sortedResponse->json('data'))->pluck('name')->values()->all();
        $alphaPosition = array_search('zzsort.alpha.active', $names, true);
        $zetaPosition = array_search('zzsort.zeta.active', $names, true);
        $this->assertIsInt($alphaPosition);
        $this->assertIsInt($zetaPosition);
        $this->assertLessThan($zetaPosition, $alphaPosition);

        $trashedResponse = $this->actingAs($actor)
            ->getJson('/api/v1/permissions?only_trashed=true&per_page=100');

        $trashedResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $deletedPermission->id);
    }
}
