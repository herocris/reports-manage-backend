<?php

namespace Tests\Feature\Api\V1\ActivityLog;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing activity log entries.
 */
class IndexActivityLogTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_activity_logs(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);

        activity()->causedBy($actor)->log('Test activity');

        $response = $this->actingAs($actor)->getJson('/api/v1/activity-logs');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_activity_log_response_includes_causer_name(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);

        activity()->causedBy($actor)->log('User logged in');

        $response = $this->actingAs($actor)->getJson('/api/v1/activity-logs');

        $response->assertOk()
            ->assertJsonFragment([
                'description' => 'User logged in',
                'causer' => ['id' => $actor->id, 'name' => $actor->name, 'email' => $actor->email],
            ]);
    }

    public function test_activity_log_response_has_expected_structure(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);

        activity()->causedBy($actor)->log('Test structure');

        $response = $this->actingAs($actor)->getJson('/api/v1/activity-logs');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'log_name', 'description', 'event', 'subject_type', 'subject_id', 'causer', 'properties', 'created_at', 'updated_at']],
            ]);
    }

    public function test_unauthenticated_user_cannot_list_activity_logs(): void
    {
        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_list_activity_logs(): void
    {
        $actor = User::factory()->create();

        $response = $this->actingAs($actor)->getJson('/api/v1/activity-logs');

        $response->assertForbidden();
    }

    public function test_activity_log_is_paginated(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);

        for ($i = 0; $i < 3; $i++) {
            activity()->causedBy($actor)->log("Activity {$i}");
        }

        $response = $this->actingAs($actor)->getJson('/api/v1/activity-logs');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 25);
    }
}
