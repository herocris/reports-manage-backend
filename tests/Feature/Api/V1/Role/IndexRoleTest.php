<?php

namespace Tests\Feature\Api\V1\Role;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering role records.
 */
class IndexRoleTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    private const GUARD_NAME = 'sanctum';

    public function test_authenticated_user_can_list_roles(): void
    {
        $actor = $this->createAuthorizedUser(['roles.view']);
        Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);
        Role::create(['name' => 'manager', 'guard_name' => self::GUARD_NAME]);

        $response = $this->actingAs($actor)->getJson('/api/v1/roles');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_index_supports_include_permissions(): void
    {
        $actor = $this->createAuthorizedUser(['roles.view']);
        $permission = Permission::findOrCreate('users.view', self::GUARD_NAME);
        $role = Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);
        $role->givePermissionTo($permission);

        $this->actingAs($actor)
            ->getJson('/api/v1/roles?include=permissions&filter[name]=admin&per_page=100')
            ->assertOk()
            ->assertJsonPath('data.0.permissions.0', 'users.view');
    }

    public function test_index_supports_sparse_fields_selection(): void
    {
        $actor = $this->createAuthorizedUser(['roles.view']);
        Role::create(['name' => 'admin', 'guard_name' => self::GUARD_NAME]);

        $this->actingAs($actor)
            ->getJson('/api/v1/roles?fields=id,name&per_page=100')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonMissingPath('data.0.guard_name');
    }

    public function test_index_supports_filter_by_permission(): void
    {
        $actor = $this->createAuthorizedUser(['roles.view']);
        $viewPermission = Permission::findOrCreate('reports.view', self::GUARD_NAME);
        $exportPermission = Permission::findOrCreate('reports.export', self::GUARD_NAME);
        $viewer = Role::create(['name' => 'viewer', 'guard_name' => self::GUARD_NAME]);
        $exporter = Role::create(['name' => 'exporter', 'guard_name' => self::GUARD_NAME]);
        $viewer->givePermissionTo($viewPermission);
        $exporter->givePermissionTo($exportPermission);

        $response = $this->actingAs($actor)
            ->getJson('/api/v1/roles?filter[permission]=reports.view&per_page=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'viewer');
    }

    public function test_index_supports_only_trashed_and_sorting(): void
    {
        $actor = $this->createAuthorizedUser(['roles.view']);
        Role::create(['name' => 'zzsort-alpha-active', 'guard_name' => self::GUARD_NAME]);
        Role::create(['name' => 'zzsort-zeta-active', 'guard_name' => self::GUARD_NAME]);
        $deletedRole = Role::create(['name' => 'zzsort-deleted', 'guard_name' => self::GUARD_NAME]);
        $deletedRole->delete();

        $sortedResponse = $this->actingAs($actor)
            ->getJson('/api/v1/roles?sort=name&direction=asc&per_page=100');

        $sortedResponse->assertOk();
        $names = collect($sortedResponse->json('data'))->pluck('name')->values()->all();
        $alphaPosition = array_search('zzsort-alpha-active', $names, true);
        $zetaPosition = array_search('zzsort-zeta-active', $names, true);
        $this->assertIsInt($alphaPosition);
        $this->assertIsInt($zetaPosition);
        $this->assertLessThan($zetaPosition, $alphaPosition);

        $trashedResponse = $this->actingAs($actor)
            ->getJson('/api/v1/roles?only_trashed=true&per_page=100');

        $trashedResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $deletedRole->id);
    }

    public function test_unauthenticated_user_cannot_list_roles(): void
    {
        $response = $this->getJson('/api/v1/roles');

        $response->assertUnauthorized();
    }
}
