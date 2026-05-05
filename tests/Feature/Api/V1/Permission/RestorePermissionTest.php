<?php

namespace Tests\Feature\Api\V1\Permission;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted permission records.
 */
class RestorePermissionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_permission(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.restore']);
        $permission = Permission::findOrCreate('temp.restore-test', 'sanctum');
        $permission->delete();

        $this->actingAs($actor)->postJson("/api/v1/permissions/{$permission->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Permission restored successfully.']);

        $this->assertNotSoftDeleted($permission);
    }

    public function test_user_without_permission_cannot_restore_permission(): void
    {
        $actor = User::factory()->create();
        $permission = Permission::findOrCreate('temp.restore-test2', 'sanctum');
        $permission->delete();

        $this->actingAs($actor)->postJson("/api/v1/permissions/{$permission->id}/restore")
            ->assertForbidden();
    }
}
