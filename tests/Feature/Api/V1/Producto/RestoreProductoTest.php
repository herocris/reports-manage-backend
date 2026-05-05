<?php

namespace Tests\Feature\Api\V1\Producto;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted producto records.
 */
class RestoreProductoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_producto(): void
    {
        $actor = $this->createAuthorizedUser(['productos.restore']);
        $producto = Producto::factory()->create();
        $producto->delete();

        $this->actingAs($actor)->postJson("/api/v1/productos/{$producto->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Producto restored successfully.']);

        $this->assertNotSoftDeleted($producto);
    }

    public function test_user_without_permission_cannot_restore_producto(): void
    {
        $actor = User::factory()->create();
        $producto = Producto::factory()->create();
        $producto->delete();

        $this->actingAs($actor)->postJson("/api/v1/productos/{$producto->id}/restore")
            ->assertForbidden();
    }
}
