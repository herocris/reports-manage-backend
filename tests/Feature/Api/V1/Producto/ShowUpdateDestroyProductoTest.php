<?php

namespace Tests\Feature\Api\V1\Producto;

use App\Models\Producto;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, and soft-deleting producto records.
 */
class ShowUpdateDestroyProductoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_producto(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view', 'productos.update', 'productos.delete']);
        $producto = Producto::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/productos/{$producto->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/productos/{$producto->id}", ['nombre' => 'Monitor'])
            ->assertOk()->assertJsonFragment(['nombre' => 'Monitor']);
        $this->actingAs($actor)->deleteJson("/api/v1/productos/{$producto->id}")->assertOk();

        $this->assertSoftDeleted($producto);
    }

    public function test_show_returns_404_for_nonexistent_producto(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);

        $this->actingAs($actor)->getJson('/api/v1/productos/999999')->assertNotFound();
    }
}
