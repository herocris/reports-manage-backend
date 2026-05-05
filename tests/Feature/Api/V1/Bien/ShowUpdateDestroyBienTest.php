<?php

namespace Tests\Feature\Api\V1\Bien;

use App\Models\Bien;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting bien records.
 */
class ShowUpdateDestroyBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_bien(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.view', 'bienes.update', 'bienes.delete']);
        $bien = Bien::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/bienes/{$bien->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/bienes/{$bien->id}", ['descripcion' => 'Actualizado'])
            ->assertOk()->assertJsonFragment(['descripcion' => 'Actualizado']);
        $this->actingAs($actor)->deleteJson("/api/v1/bienes/{$bien->id}")->assertOk();

        $this->assertSoftDeleted($bien);
    }

    public function test_show_returns_404_for_nonexistent_bien(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.view']);

        $this->actingAs($actor)->getJson('/api/v1/bienes/999999')->assertNotFound();
    }
}
