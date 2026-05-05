<?php

namespace Tests\Feature\Api\V1\Ubicacion;

use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting ubicacion records.
 */
class ShowUpdateDestroyUbicacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_ubicacion(): void
    {
        $actor = $this->createAuthorizedUser(['ubicaciones.view', 'ubicaciones.update', 'ubicaciones.delete']);
        $ubicacion = Ubicacion::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/ubicaciones/{$ubicacion->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/ubicaciones/{$ubicacion->id}", ['descripcion' => 'Zona norte'])
            ->assertOk()->assertJsonFragment(['descripcion' => 'Zona norte']);
        $this->actingAs($actor)->deleteJson("/api/v1/ubicaciones/{$ubicacion->id}")->assertOk();

        $this->assertSoftDeleted($ubicacion);
    }

    public function test_show_returns_404_for_nonexistent_ubicacion(): void
    {
        $actor = $this->createAuthorizedUser(['ubicaciones.view']);

        $this->actingAs($actor)->getJson('/api/v1/ubicaciones/999999')->assertNotFound();
    }
}
