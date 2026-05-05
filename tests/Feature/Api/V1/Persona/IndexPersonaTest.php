<?php

namespace Tests\Feature\Api\V1\Persona;

use App\Models\Informe;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering persona records.
 */
class IndexPersonaTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_personas(): void
    {
        $actor = $this->createAuthorizedUser(['personas.view']);
        $informe = Informe::factory()->create();
        $personas = Persona::factory()->count(2)->create();
        $informe->personas()->attach($personas->pluck('id')->all());

        $this->actingAs($actor)->getJson("/api/v1/personas?informe_id={$informe->id}")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_personas(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/personas')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/personas?informe_id=1')->assertForbidden();
    }
}
