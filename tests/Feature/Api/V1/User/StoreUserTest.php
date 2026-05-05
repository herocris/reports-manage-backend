<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new user accounts.
 */
class StoreUserTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_a_user(): void
    {
        $actor = $this->createAuthorizedUser(['users.create']);
        $institucion = Institucion::factory()->create();

        $this->actingAs($actor)->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password1!',
            'institucion_id' => $institucion->id,
        ])->assertCreated()->assertJsonFragment(['email' => 'john@example.com']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'institucion_id' => $institucion->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['users.create']);

        $this->actingAs($actor)->postJson('/api/v1/users', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'institucion_id']);
    }

    public function test_store_validates_unique_email(): void
    {
        $actor = $this->createAuthorizedUser(['users.create']);
        $institucion = Institucion::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($actor)->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'taken@example.com',
            'password' => 'Password1!',
            'institucion_id' => $institucion->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_unauthenticated_user_cannot_create_a_user(): void
    {
        $institucion = Institucion::factory()->create();

        $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password1!',
            'institucion_id' => $institucion->id,
        ])->assertUnauthorized();
    }
}
