<?php

namespace Tests\Feature\Api\V1\TipoBien;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new tipo de bien records.
 */
class StoreTipoBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_tipo_bien(): void
    {
        $actor = $this->createAuthorizedUser(['tipos_bien.create']);

        $this->actingAs($actor)->postJson('/api/v1/tipos-bien', [
            'nombre' => 'Vehiculo',
            'descripcion' => 'Tipo de bien vehicular',
        ])->assertCreated()->assertJsonFragment(['nombre' => 'Vehiculo']);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['tipos_bien.create']);

        $this->actingAs($actor)->postJson('/api/v1/tipos-bien', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'descripcion']);
    }
}
