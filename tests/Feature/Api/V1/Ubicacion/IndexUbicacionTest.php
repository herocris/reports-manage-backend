<?php

namespace Tests\Feature\Api\V1\Ubicacion;

use App\Models\Informe;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering ubicacion records.
 */
class IndexUbicacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_ubicaciones(): void
    {
        $actor = $this->createAuthorizedUser(['ubicaciones.view']);
        $informe = Informe::factory()->create();
        Ubicacion::factory()->count(2)->create(['informe_id' => $informe->id]);

        $this->actingAs($actor)->getJson("/api/v1/ubicaciones?informe_id={$informe->id}")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_ubicaciones(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/ubicaciones')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/ubicaciones?informe_id=1')->assertForbidden();
    }
}
