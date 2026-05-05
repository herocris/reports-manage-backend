<?php

namespace Tests\Feature\Api\V1\Institucion;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new institucion records.
 */
class StoreInstitucionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_institucion(): void
    {
        $actor = $this->createAuthorizedUser(['instituciones.create']);

        $this->actingAs($actor)->postJson('/api/v1/instituciones', [
            'nombre' => 'Ministerio Publico',
            'contacto' => 'Carlos Ruiz',
            'telefono' => '8854-9563',
        ])->assertCreated()->assertJsonFragment(['telefono' => '8854-9563']);
    }

    public function test_institucion_validates_phone_format(): void
    {
        $actor = $this->createAuthorizedUser(['instituciones.create']);

        $this->actingAs($actor)->postJson('/api/v1/instituciones', [
            'nombre' => 'Ministerio Publico',
            'contacto' => 'Carlos Ruiz',
            'telefono' => '88549563',
        ])->assertUnprocessable()->assertJsonValidationErrors(['telefono']);
    }
}
