<?php

namespace Tests\Feature\Api\V1\Ubicacion;

use App\Models\Informe;
use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new ubicacion records.
 */
class StoreUbicacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_ubicacion(): void
    {
        $actor = $this->createAuthorizedUser(['ubicaciones.create']);
        $informe = Informe::factory()->create();

        $response = $this->actingAs($actor)->postJson('/api/v1/ubicaciones', [
            'latitud' => '14.306330',
            'longitud' => '-86.551515',
            'descripcion' => 'Zona central',
            'informe_id' => $informe->id,
        ]);

        $response->assertCreated()->assertJsonFragment(['descripcion' => 'Zona central']);

        $ubicacion = Ubicacion::query()->where('descripcion', 'Zona central')->firstOrFail();
        $this->assertSame($informe->id, $ubicacion->informe_id);
    }

    public function test_ubicacion_validates_coordinates_format(): void
    {
        $actor = $this->createAuthorizedUser(['ubicaciones.create']);

        $this->actingAs($actor)->postJson('/api/v1/ubicaciones', [
            'latitud' => 150,
            'longitud' => 200,
            'descripcion' => 'Fuera de rango',
        ])->assertUnprocessable()->assertJsonValidationErrors(['latitud', 'longitud', 'informe_id']);
    }
}
