<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving a single user record.
 */
class ShowUserTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_a_user(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        $user = User::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);

        $this->actingAs($actor)->getJson('/api/v1/users/99999')->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_view_a_user(): void
    {
        $user = User::factory()->create();

        $this->getJson("/api/v1/users/{$user->id}")->assertUnauthorized();
    }
}
