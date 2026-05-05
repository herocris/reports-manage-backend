<?php

namespace Tests\Feature\Api\V1\Observacion;

use App\Models\Informe;
use App\Models\User;
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
        $user = User::factory()->create();

        $this->actingAs($actor)->postJson('/api/v1/observaciones', [
            'descripcion' => 'Una observación importante.',
            'usuario_id'  => $user->id,
            'informe_id'  => $informe->id,
        ])->assertCreated()->assertJsonFragment(['descripcion' => 'Una observación importante.']);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.create']);

        $this->actingAs($actor)->postJson('/api/v1/observaciones', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['descripcion', 'usuario_id', 'informe_id']);
    }
}
