<?php

namespace Tests\Feature\Api\V1\Producto;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new producto records.
 */
class StoreProductoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_producto(): void
    {
        $actor = $this->createAuthorizedUser(['productos.create']);

        $this->actingAs($actor)->postJson('/api/v1/productos', [
            'nombre' => 'Laptop',
            'descripcion' => 'Equipo de computo',
        ])->assertCreated()->assertJsonFragment(['nombre' => 'Laptop']);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['productos.create']);

        $this->actingAs($actor)->postJson('/api/v1/productos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'descripcion']);
    }
}
