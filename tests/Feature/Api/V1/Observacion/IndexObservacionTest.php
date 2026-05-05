<?php

namespace Tests\Feature\Api\V1\Observacion;

use App\Models\Observacion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering observacion records.
 */
class IndexObservacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_observaciones(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.view']);
        Observacion::factory()->count(2)->create();

        $this->actingAs($actor)->getJson('/api/v1/observaciones')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_observaciones(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/observaciones')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/observaciones')->assertForbidden();
    }
}
