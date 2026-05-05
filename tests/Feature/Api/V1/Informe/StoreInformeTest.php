<?php

namespace Tests\Feature\Api\V1\Informe;

use App\Models\Ambito;
use App\Models\Informe;
use App\Models\Producto;
use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new informe records.
 */
class StoreInformeTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    private function validPayload(): array
    {
        $producto = Producto::factory()->create();

        return [
            'expediente'   => 'EXP-ABC123',
            'titulo'       => 'Informe de prueba',
            'producto_id'  => $producto->id,
            'fecha_inicio' => '2025-01-01',
            'fecha_final'  => '2025-06-30',
        ];
    }

    public function test_authenticated_user_can_create_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.create']);
        $payload = $this->validPayload();

        $this->actingAs($actor)->postJson('/api/v1/informes', $payload)
            ->assertCreated()
            ->assertJsonFragment(['expediente' => 'EXP-ABC123']);

        $informe = Informe::query()->where('expediente', 'EXP-ABC123')->firstOrFail();
        $this->assertSame($actor->id, $informe->usuariocreador_id);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['informes.create']);

        $this->actingAs($actor)->postJson('/api/v1/informes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['expediente', 'titulo', 'producto_id', 'fecha_inicio', 'fecha_final']);
    }

    public function test_store_validates_expediente_format(): void
    {
        $actor = $this->createAuthorizedUser(['informes.create']);
        $payload = $this->validPayload();
        $payload['expediente'] = 'INVALID-FORMAT';

        $this->actingAs($actor)->postJson('/api/v1/informes', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['expediente']);
    }

    public function test_store_validates_expediente_is_unique(): void
    {
        $actor = $this->createAuthorizedUser(['informes.create']);
        Informe::factory()->create(['expediente' => 'EXP-ABC123']);
        $payload = $this->validPayload();

        $this->actingAs($actor)->postJson('/api/v1/informes', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['expediente']);
    }

    public function test_store_validates_fecha_final_after_fecha_inicio(): void
    {
        $actor = $this->createAuthorizedUser(['informes.create']);
        $payload = $this->validPayload();
        $payload['fecha_final'] = '2024-01-01';

        $this->actingAs($actor)->postJson('/api/v1/informes', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fecha_final']);
    }

    public function test_can_create_informe_with_relations(): void
    {
        $actor = $this->createAuthorizedUser(['informes.create']);
        $ambito = Ambito::factory()->create();
        $ubicacion = Ubicacion::factory()->create();
        $payload = $this->validPayload();
        $payload['ambitos'] = [$ambito->id];

        $response = $this->actingAs($actor)->postJson('/api/v1/informes', $payload);

        $response->assertCreated();
        $informe = Informe::where('expediente', 'EXP-ABC123')->first();
        $ubicacion->update(['informe_id' => $informe->id]);
        $this->assertTrue($informe->ambitos()->where('ambito_id', $ambito->id)->exists());
        $this->assertTrue($informe->ubicaciones()->where('id', $ubicacion->id)->exists());
    }
}
