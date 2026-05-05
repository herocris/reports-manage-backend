<?php

namespace Tests\Feature\Api\V1\Bitacora;

use App\Models\Bitacora;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting bitacora records.
 */
class ShowUpdateDestroyBitacoraTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_bitacora(): void
    {
        $actor = $this->createAuthorizedUser(['bitacoras.view', 'bitacoras.update', 'bitacoras.delete']);
        $bitacora = Bitacora::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/bitacoras/{$bitacora->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/bitacoras/{$bitacora->id}", ['descripcion' => 'Actualizada'])
            ->assertOk()->assertJsonFragment(['descripcion' => 'Actualizada']);
        $this->actingAs($actor)->deleteJson("/api/v1/bitacoras/{$bitacora->id}")->assertOk();

        $this->assertSoftDeleted($bitacora);
    }

    public function test_show_returns_404_for_nonexistent_bitacora(): void
    {
        $actor = $this->createAuthorizedUser(['bitacoras.view']);

        $this->actingAs($actor)->getJson('/api/v1/bitacoras/999999')->assertNotFound();
    }
}
