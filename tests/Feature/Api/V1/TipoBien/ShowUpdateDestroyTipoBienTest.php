<?php

namespace Tests\Feature\Api\V1\TipoBien;

use App\Models\TipoBien;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting tipo de bien records.
 */
class ShowUpdateDestroyTipoBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_tipo_bien(): void
    {
        $actor = $this->createAuthorizedUser(['tipos_bien.view', 'tipos_bien.update', 'tipos_bien.delete']);
        $tipoBien = TipoBien::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/tipos-bien/{$tipoBien->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/tipos-bien/{$tipoBien->id}", ['nombre' => 'Inmueble'])
            ->assertOk()->assertJsonFragment(['nombre' => 'Inmueble']);
        $this->actingAs($actor)->deleteJson("/api/v1/tipos-bien/{$tipoBien->id}")->assertOk();

        $this->assertSoftDeleted($tipoBien);
    }

    public function test_show_returns_404_for_nonexistent_tipo_bien(): void
    {
        $actor = $this->createAuthorizedUser(['tipos_bien.view']);

        $this->actingAs($actor)->getJson('/api/v1/tipos-bien/999999')->assertNotFound();
    }
}
