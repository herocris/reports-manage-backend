<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for soft-deleting user accounts.
 */
class DestroyUserTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_delete_a_user(): void
    {
        $actor = $this->createAuthorizedUser(['users.delete']);
        $user = User::factory()->create();

        $this->actingAs($actor)->deleteJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJson(['message' => 'User deleted successfully.']);

        $this->assertSoftDeleted($user);
    }

    public function test_unauthenticated_user_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$user->id}")->assertUnauthorized();
    }

    public function test_authenticated_user_without_permission_cannot_manage_users(): void
    {
        $actor = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($actor)->getJson('/api/v1/users')->assertForbidden();
        $this->actingAs($actor)->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password1!',
        ])->assertForbidden();
        $this->actingAs($actor)->getJson("/api/v1/users/{$user->id}")->assertForbidden();
        $this->actingAs($actor)->putJson("/api/v1/users/{$user->id}", ['name' => 'Updated'])->assertForbidden();
        $this->actingAs($actor)->deleteJson("/api/v1/users/{$user->id}")->assertForbidden();
    }
}
