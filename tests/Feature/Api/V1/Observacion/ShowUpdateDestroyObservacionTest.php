<?php

namespace Tests\Feature\Api\V1\Observacion;

use App\Models\Observacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting observacion records.
 */
class ShowUpdateDestroyObservacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_observacion(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.view', 'observaciones.update', 'observaciones.delete']);
        $observacion = Observacion::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/observaciones/{$observacion->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/observaciones/{$observacion->id}", ['descripcion' => 'Actualizada'])
            ->assertOk()->assertJsonFragment(['descripcion' => 'Actualizada']);
        $this->actingAs($actor)->deleteJson("/api/v1/observaciones/{$observacion->id}")->assertOk();

        $this->assertSoftDeleted($observacion);
    }

    public function test_show_returns_404_for_nonexistent_observacion(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.view']);

        $this->actingAs($actor)->getJson('/api/v1/observaciones/999999')->assertNotFound();
    }
}
