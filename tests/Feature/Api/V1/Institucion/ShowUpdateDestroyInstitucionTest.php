<?php

namespace Tests\Feature\Api\V1\Institucion;

use App\Models\Institucion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting institucion records.
 */
class ShowUpdateDestroyInstitucionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_institucion(): void
    {
        $actor = $this->createAuthorizedUser(['instituciones.view', 'instituciones.update', 'instituciones.delete']);
        $institucion = Institucion::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/instituciones/{$institucion->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/instituciones/{$institucion->id}", ['contacto' => 'Ana Gomez'])
            ->assertOk()->assertJsonFragment(['contacto' => 'Ana Gomez']);
        $this->actingAs($actor)->deleteJson("/api/v1/instituciones/{$institucion->id}")->assertOk();

        $this->assertSoftDeleted($institucion);
    }

    public function test_show_returns_404_for_nonexistent_institucion(): void
    {
        $actor = $this->createAuthorizedUser(['instituciones.view']);

        $this->actingAs($actor)->getJson('/api/v1/instituciones/999999')->assertNotFound();
    }
}
