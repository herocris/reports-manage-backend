<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature tests for the authenticated user's profile endpoint
 * and password update functionality.
 */
class ProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_authenticated_user_can_update_only_own_password(): void
    {
        $user = User::factory()->create(['password' => 'Password1!']);
        $user->assignRole(Role::findOrCreate('admin', 'sanctum'));

        $response = $this->actingAs($user)->putJson('/api/v1/me', [
            'password' => 'UpdatedPassword1!',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);

        $this->assertTrue(Hash::check('UpdatedPassword1!', $user->fresh()->password));
        $this->assertCount(1, $user->fresh()->roles);
    }

    public function test_authenticated_user_cannot_update_name_email_roles_or_institucion_from_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/v1/me', [
            'name' => 'Otro Nombre',
            'email' => 'otro@example.com',
            'roles' => ['admin'],
            'institucion_id' => 99,
            'password' => 'UpdatedPassword1!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'roles', 'institucion_id']);
    }

    public function test_unauthenticated_user_cannot_fetch_profile(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $this->putJson('/api/v1/me', [
            'password' => 'UpdatedPassword1!',
        ])->assertUnauthorized();
    }
}
