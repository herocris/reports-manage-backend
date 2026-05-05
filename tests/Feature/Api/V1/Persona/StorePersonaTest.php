<?php

namespace Tests\Feature\Api\V1\Persona;

use App\Models\Informe;
use App\Models\Persona;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new persona records.
 */
class StorePersonaTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_persona(): void
    {
        $actor = $this->createAuthorizedUser(['personas.create']);
        $informe = Informe::factory()->create();

        $response = $this->actingAs($actor)->postJson('/api/v1/personas', [
            'nombre' => 'Juan Perez',
            'numero_identificacion' => '0801-1987-25487',
            'informe_id' => $informe->id,
        ]);

        $response->assertCreated()->assertJsonFragment(['numero_identificacion' => '0801-1987-25487']);

        $persona = Persona::query()->where('numero_identificacion', '0801-1987-25487')->firstOrFail();
        $this->assertTrue($informe->personas()->where('personas.id', $persona->id)->exists());
    }

    public function test_persona_validates_identification_format(): void
    {
        $actor = $this->createAuthorizedUser(['personas.create']);

        $this->actingAs($actor)->postJson('/api/v1/personas', [
            'nombre' => 'Juan Perez',
            'numero_identificacion' => '12345',
        ])->assertUnprocessable()->assertJsonValidationErrors(['numero_identificacion', 'informe_id']);
    }
}
