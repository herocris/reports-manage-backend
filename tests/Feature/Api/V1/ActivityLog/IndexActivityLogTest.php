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
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_index_supports_search_sorting_and_custom_pagination(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);

        activity()->causedBy($actor)->useLog('audit')->event('updated')->log('Zeta searchable activity');
        activity()->causedBy($actor)->useLog('audit')->event('created')->log('Alpha searchable activity');

        $this->actingAs($actor)
            ->getJson('/api/v1/activity-logs?search=searchable&sort=description&direction=ASC&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Alpha searchable activity')
            ->assertJsonPath('meta.per_page', 1);
    }

    public function test_index_supports_subject_and_event_filters(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);
        $subject = User::factory()->create();

        activity()
            ->causedBy($actor)
            ->performedOn($subject)
            ->event('updated')
            ->log('Matching activity');

        activity()
            ->causedBy($actor)
            ->performedOn($subject)
            ->event('created')
            ->log('Non-matching activity');

        $query = http_build_query([
            'filter' => [
                'subject_type' => 'User',
                'subject_id' => $subject->id,
                'event' => 'updated',
            ],
        ]);

        $this->actingAs($actor)
            ->getJson("/api/v1/activity-logs?{$query}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Matching activity')
            ->assertJsonPath('data.0.subject_type', 'User');
    }

    public function test_index_rejects_invalid_query_parameters(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);

        $this->actingAs($actor)
            ->getJson('/api/v1/activity-logs?page=0&per_page=101&sort=invalid&direction=sideways&filter[subject_id]=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'page',
                'per_page',
                'sort',
                'direction',
                'filter.subject_id',
            ]);
    }

    public function test_unknown_subject_type_returns_an_empty_list(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);

        activity()
            ->causedBy($actor)
            ->performedOn($actor)
            ->event('updated')
            ->log('Existing user activity');

        $this->actingAs($actor)
            ->getJson('/api/v1/activity-logs?filter[subject_type]=Us')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);

        $query = http_build_query([
            'filter' => ['subject_type' => User::class],
        ]);

        $this->actingAs($actor)
            ->getJson("/api/v1/activity-logs?{$query}")
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }
}
