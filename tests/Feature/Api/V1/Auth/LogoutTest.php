<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for web and mobile logout flows,
 * including per-device token revocation.
 */
class LogoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_logout_on_mobile_endpoint(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('mobile-device');
        $otherToken = $user->createToken('other-device');

        $response = $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/v1/auth/mobile/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        // Only the token used in the request is revoked.
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);

        // Tokens from other devices remain active.
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'event' => 'logout',
            'description' => 'auth.logout',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_logout_on_web_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/auth/web/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ])
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertGuest('web');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'event' => 'logout',
            'description' => 'auth.logout',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }

    public function test_legacy_logout_endpoint_remains_available_for_mobile_clients(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token');

        $response = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/auth/mobile/logout');

        $response->assertUnauthorized();
    }
}
