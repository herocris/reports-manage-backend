<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted user accounts.
 */
class RestoreUserTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_user(): void
    {
        $actor = $this->createAuthorizedUser(['users.restore']);
        $user = User::factory()->create();
        $user->delete();

        $this->actingAs($actor)->postJson("/api/v1/users/{$user->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'User restored successfully.']);

        $this->assertNotSoftDeleted($user);
    }

    public function test_user_without_permission_cannot_restore_user(): void
    {
        $actor = User::factory()->create();
        $user = User::factory()->create();
        $user->delete();

        $this->actingAs($actor)->postJson("/api/v1/users/{$user->id}/restore")
            ->assertForbidden();
    }
}
