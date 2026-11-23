<?php

namespace Tests\Feature\Api\V1\Observacion;

use App\Models\Informe;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new observacion records.
 */
class StoreObservacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_observacion(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.create']);
        $informe = Informe::factory()->create();

        $this->actingAs($actor)->postJson('/api/v1/observaciones', [
            'titulo' => 'Corregir resultados',
            'descripcion' => 'Una observación importante.',
            'informe_id' => $informe->id,
        ])->assertCreated()->assertJsonPath('data.numero', 1)
            ->assertJsonPath('data.usuario_id', $actor->id)
            ->assertJsonFragment(['titulo' => 'Corregir resultados']);

        $this->actingAs($actor)->postJson('/api/v1/observaciones', [
            'titulo' => 'Segunda observación',
            'descripcion' => 'Otra observación.',
            'informe_id' => $informe->id,
        ])->assertCreated()->assertJsonPath('data.numero', 2);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.create']);

        $this->actingAs($actor)->postJson('/api/v1/observaciones', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['titulo', 'descripcion', 'informe_id']);
    }
}
