<?php

namespace Tests\Feature\Api\V1\Ambito;

use App\Models\Ambito;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting ambito records.
 */
class ShowUpdateDestroyAmbitoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_ambito(): void
    {
        $actor = $this->createAuthorizedUser(['ambitos.view', 'ambitos.update', 'ambitos.delete']);
        $ambito = Ambito::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/ambitos/{$ambito->id}")
            ->assertOk();

        $this->actingAs($actor)->putJson("/api/v1/ambitos/{$ambito->id}", [
            'nombre' => 'Regional',
        ])->assertOk()->assertJsonFragment(['nombre' => 'Regional']);

        $this->actingAs($actor)->deleteJson("/api/v1/ambitos/{$ambito->id}")
            ->assertOk();

        $this->assertSoftDeleted($ambito);
    }

    public function test_show_returns_404_for_nonexistent_ambito(): void
    {
        $actor = $this->createAuthorizedUser(['ambitos.view']);

        $this->actingAs($actor)->getJson('/api/v1/ambitos/999999')->assertNotFound();
    }
}
