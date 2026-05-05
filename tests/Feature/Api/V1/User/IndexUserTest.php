<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering user records.
 */
class IndexUserTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_users(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        User::factory()->count(3)->create();

        $this->actingAs($actor)->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_index_supports_include_roles_and_permissions(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        $targetUser = User::factory()->create(['email' => 'with-relations@example.com']);
        $role = Role::findOrCreate('admin', 'sanctum');
        $permission = Permission::findOrCreate('reports.view', 'sanctum');
        $targetUser->assignRole($role);
        $targetUser->givePermissionTo($permission);

        $this->actingAs($actor)
            ->getJson('/api/v1/users?include=roles,permissions&filter[email]=with-relations@example.com&per_page=100')
            ->assertOk()
            ->assertJsonPath('data.0.roles.0', 'admin')
            ->assertJsonPath('data.0.permissions.0', 'reports.view');
    }

    public function test_index_supports_sparse_fields_selection(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        User::factory()->create();

        $this->actingAs($actor)
            ->getJson('/api/v1/users?fields=id,name&per_page=100')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonMissingPath('data.0.email')
            ->assertJsonMissingPath('data.0.created_at');
    }

    public function test_index_supports_filter_by_role(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        $adminRole = Role::findOrCreate('admin', 'sanctum');
        $editorRole = Role::findOrCreate('editor', 'sanctum');

        $adminUser = User::factory()->create();
        $editorUser = User::factory()->create();
        $adminUser->assignRole($adminRole);
        $editorUser->assignRole($editorRole);

        $this->actingAs($actor)
            ->getJson('/api/v1/users?filter[role]=admin&per_page=100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $adminUser->id);
    }

    public function test_index_supports_sorting_and_only_trashed(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        User::factory()->create(['email' => 'zzsort-alpha-active@example.com']);
        User::factory()->create(['email' => 'zzsort-zeta-active@example.com']);
        $deletedUser = User::factory()->create(['email' => 'zzsort-deleted@example.com']);
        $deletedUser->delete();

        $sortedResponse = $this->actingAs($actor)
            ->getJson('/api/v1/users?sort=email&direction=asc&per_page=100');

        $sortedResponse->assertOk();
        $emails = collect($sortedResponse->json('data'))->pluck('email')->values()->all();
        $alphaPosition = array_search('zzsort-alpha-active@example.com', $emails, true);
        $zetaPosition = array_search('zzsort-zeta-active@example.com', $emails, true);
        $this->assertIsInt($alphaPosition);
        $this->assertIsInt($zetaPosition);
        $this->assertLessThan($zetaPosition, $alphaPosition);

        $trashedResponse = $this->actingAs($actor)
            ->getJson('/api/v1/users?only_trashed=true&per_page=100');

        $trashedResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $deletedUser->id);
    }

    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();
    }
}
