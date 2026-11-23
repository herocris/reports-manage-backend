<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for web and mobile registration flows.
 */
class RegisterTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_register_on_mobile_endpoint_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/mobile/register', [
            'name' => 'Mobile User',
            'email' => 'mobile-user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'device_name' => 'android-phone',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user' => ['id', 'name', 'email']],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'mobile-user@example.com',
            'name' => 'Mobile User',
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'android-phone',
        ]);
    }

    public function test_user_can_register_on_web_endpoint_and_authenticate_with_session(): void
    {
        $response = $this->postJson('/api/v1/auth/web/register', [
            'name' => 'Web User',
            'email' => 'web-user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user' => ['id', 'name', 'email', 'roles', 'permissions']],
            ])
            ->assertJsonMissingPath('data.token');

        $user = User::where('email', 'web-user@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_registration_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/mobile/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/mobile/register', [
            'name' => 'No Confirm User',
            'email' => 'no-confirm@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/v1/auth/mobile/register', [
            'name' => 'Duplicate User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
