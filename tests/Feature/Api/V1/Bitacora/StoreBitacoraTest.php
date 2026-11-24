<?php

namespace Tests\Feature\Api\V1\Bitacora;

use App\Models\Informe;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new bitacora log entries.
 */
class StoreBitacoraTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_bitacora(): void
    {
        $actor = $this->createAuthorizedUser(['bitacoras.create']);
        $informe = Informe::factory()->create();

        $this->actingAs($actor)->postJson('/api/v1/bitacoras', [
            'descripcion' => 'Entrada de bitácora.',
            'fecha' => '2025-03-15',
            'informe_id' => $informe->id,
        ])->assertCreated()->assertJsonFragment(['descripcion' => 'Entrada de bitácora.']);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['bitacoras.create']);

        $this->actingAs($actor)->postJson('/api/v1/bitacoras', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['descripcion', 'fecha', 'informe_id']);
    }
}
