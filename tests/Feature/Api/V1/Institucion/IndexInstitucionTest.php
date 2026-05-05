<?php

namespace Tests\Feature\Api\V1\Institucion;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering institucion records.
 */
class IndexInstitucionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_instituciones(): void
    {
        $actor = $this->createAuthorizedUser(['instituciones.view']);
        Institucion::factory()->count(2)->create();

        $this->actingAs($actor)->getJson('/api/v1/instituciones')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_instituciones(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/instituciones')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/instituciones')->assertForbidden();
    }
}
