<?php

namespace Tests\Feature\Api\V1\Role;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted role records.
 */
class RestoreRoleTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    private const GUARD_NAME = 'sanctum';

    public function test_authorized_user_can_restore_a_deleted_role(): void
    {
        $actor = $this->createAuthorizedUser(['roles.restore']);
        $role = Role::create(['name' => 'temp-role', 'guard_name' => self::GUARD_NAME]);
        $role->delete();

        $this->actingAs($actor)->postJson("/api/v1/roles/{$role->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Role restored successfully.']);

        $this->assertNotSoftDeleted($role);
    }

    public function test_user_without_permission_cannot_restore_role(): void
    {
        $actor = User::factory()->create();
        $role = Role::create(['name' => 'temp-role', 'guard_name' => self::GUARD_NAME]);
        $role->delete();

        $this->actingAs($actor)->postJson("/api/v1/roles/{$role->id}/restore")
            ->assertForbidden();
    }
}
