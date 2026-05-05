<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for updating user account fields and roles.
 */
class UpdateUserTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_update_a_user(): void
    {
        $actor = $this->createAuthorizedUser(['users.update']);
        $user = User::factory()->create();
        $institucion = Institucion::factory()->create();

        $this->actingAs($actor)->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated Name',
            'institucion_id' => $institucion->id,
        ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'institucion_id' => $institucion->id]);
    }

    public function test_update_validates_unique_email_ignoring_own(): void
    {
        $actor = $this->createAuthorizedUser(['users.update']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $this->actingAs($actor)->putJson("/api/v1/users/{$user->id}", ['email' => 'mine@example.com'])
            ->assertOk();
    }

    public function test_update_rejects_email_already_taken_by_another_user(): void
    {
        $actor = $this->createAuthorizedUser(['users.update']);
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($actor)->putJson("/api/v1/users/{$user->id}", ['email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_unauthenticated_user_cannot_update_a_user(): void
    {
        $user = User::factory()->create();

        $this->putJson("/api/v1/users/{$user->id}", ['name' => 'Updated'])->assertUnauthorized();
    }
}
