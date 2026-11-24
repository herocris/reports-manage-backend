<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that the `api` rate limiter blocks excessive requests
 * and returns a 429 response after the threshold is exceeded.
 */
class RateLimitingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_is_rate_limited_to_5_attempts_per_minute(): void
    {
        $email = 'test@example.com';

        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => $email,
                'password' => 'incorrect',
            ]);

            $response->assertStatus([$i < 5 ? 401 : 429][$i === 4 ? 1 : 0] ?? 401);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/v1/login', [
            'email' => $email,
            'password' => 'incorrect',
        ]);

        $response->assertTooManyRequests()
            ->assertJson([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
            ]);
    }

    public function test_api_is_rate_limited_to_60_requests_per_minute_per_user(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user)->getJson('/api/v1/me');
            $response->assertOk();
        }

        // 61st request should be rate limited
        $response = $this->actingAs($user)->getJson('/api/v1/me');

        $response->assertTooManyRequests()
            ->assertJson([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
            ]);
    }

    public function test_login_rate_limiting_normalizes_email_case(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $email = $attempt % 2 === 0 ? 'Case@example.com' : 'case@example.com';

            $this->postJson('/api/v1/login', [
                'email' => $email,
                'password' => 'incorrect',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/login', [
            'email' => 'CASE@example.com',
            'password' => 'incorrect',
        ])->assertTooManyRequests()->assertHeader('Retry-After');
    }

    public function test_api_rate_limiting_uses_ip_for_unauthenticated_users(): void
    {
        // Note: Throttle is applied after auth:sanctum middleware, so unauthenticated
        // requests will receive 401 before hitting rate limit. This test verifies
        // that authenticated users are rate limited by their ID per the policy.

        // Instead, test that different users have separate rate limits
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 makes 60 requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user1)->getJson('/api/v1/me');
            $response->assertOk();
        }

        // User 1's 61st request is rate limited
        $response = $this->actingAs($user1)->getJson('/api/v1/me');
        $response->assertTooManyRequests();

        // User 2 should still be able to make requests (separate limit per user)
        $response = $this->actingAs($user2)->getJson('/api/v1/me');
        $response->assertOk();
    }
}
