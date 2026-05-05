<?php

namespace Tests\Feature\Api\V1\Persona;

use App\Models\Persona;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting persona records.
 */
class ShowUpdateDestroyPersonaTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_persona(): void
    {
        $actor = $this->createAuthorizedUser(['personas.view', 'personas.update', 'personas.delete']);
        $persona = Persona::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/personas/{$persona->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/personas/{$persona->id}", ['nombre' => 'Maria Lopez'])
            ->assertOk()->assertJsonFragment(['nombre' => 'Maria Lopez']);
        $this->actingAs($actor)->deleteJson("/api/v1/personas/{$persona->id}")->assertOk();

        $this->assertSoftDeleted($persona);
    }

    public function test_show_returns_404_for_nonexistent_persona(): void
    {
        $actor = $this->createAuthorizedUser(['personas.view']);

        $this->actingAs($actor)->getJson('/api/v1/personas/999999')->assertNotFound();
    }
}
