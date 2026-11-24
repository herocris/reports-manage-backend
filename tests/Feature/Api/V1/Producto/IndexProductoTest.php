<?php

namespace Tests\Feature\Api\V1\Producto;

use App\Models\Informe;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering producto records.
 */
class IndexProductoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_productos(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);
        Producto::factory()->count(2)->create();

        $this->actingAs($actor)->getJson('/api/v1/productos')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_index_supports_pagination_query_params(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);
        Producto::factory()->count(6)->create();

        $this->actingAs($actor)
            ->getJson('/api/v1/productos?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_supports_sorting_by_allowed_field_and_direction(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);

        Producto::factory()->create(['nombre' => 'Zeta', 'descripcion' => 'x']);
        Producto::factory()->create(['nombre' => 'Alpha', 'descripcion' => 'y']);
        Producto::factory()->create(['nombre' => 'Mango', 'descripcion' => 'z']);

        $response = $this->actingAs($actor)
            ->getJson('/api/v1/productos?sort=nombre&direction=asc&per_page=100');

        $response->assertOk();
        $this->assertSame('Alpha', $response->json('data.0.nombre'));

        $response = $this->actingAs($actor)
            ->getJson('/api/v1/productos?sort=nombre&direction=desc&per_page=100');

        $response->assertOk();
        $this->assertSame('Zeta', $response->json('data.0.nombre'));
    }

    public function test_index_supports_search_across_nombre_and_descripcion(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);

        Producto::factory()->create(['nombre' => 'Laptop Forense', 'descripcion' => 'Equipo']);
        Producto::factory()->create(['nombre' => 'Impresora', 'descripcion' => 'Uso forense']);
        Producto::factory()->create(['nombre' => 'Camara', 'descripcion' => 'Fotografia']);

        $this->actingAs($actor)
            ->getJson('/api/v1/productos?search=forense&per_page=100')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_supports_explicit_business_filters(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);

        Producto::factory()->create(['nombre' => 'Laptop Judicial', 'descripcion' => 'Analisis']);
        Producto::factory()->create(['nombre' => 'Laptop', 'descripcion' => 'Fotografia forense']);
        Producto::factory()->create(['nombre' => 'Camara', 'descripcion' => 'Analisis']);

        $this->actingAs($actor)
            ->getJson('/api/v1/productos?filter[nombre]=Laptop&filter[descripcion]=Analisis&per_page=100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Laptop Judicial');
    }

    public function test_index_supports_only_trashed_for_restore_workflows(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);

        $activeProducto = Producto::factory()->create();
        $deletedProducto = Producto::factory()->create();
        $deletedProducto->delete();

        $response = $this->actingAs($actor)
            ->getJson('/api/v1/productos?only_trashed=true&per_page=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $deletedProducto->id);

        $this->assertNotEquals($activeProducto->id, $response->json('data.0.id'));
    }

    public function test_index_supports_include_relations(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);
        $producto = Producto::factory()->create();
        Informe::factory()->for($producto, 'producto')->create();

        $response = $this->actingAs($actor)
            ->getJson('/api/v1/productos?include=informes&filter[nombre]='.urlencode($producto->nombre).'&per_page=100');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['informes']]])
            ->assertJsonPath('data.0.informes.0.producto_id', $producto->id);
    }

    public function test_index_supports_sparse_fields_selection(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);
        Producto::factory()->create();

        $response = $this->actingAs($actor)
            ->getJson('/api/v1/productos?fields=id,nombre&per_page=100');

        $response->assertOk()
            ->assertJsonPath('data.0.id', (int) $response->json('data.0.id'))
            ->assertJsonStructure(['data' => [['id', 'nombre']]])
            ->assertJsonMissingPath('data.0.descripcion')
            ->assertJsonMissingPath('data.0.created_at')
            ->assertJsonMissingPath('data.0.updated_at');
    }

    public function test_index_validates_invalid_query_params(): void
    {
        $actor = $this->createAuthorizedUser(['productos.view']);

        $this->actingAs($actor)
            ->getJson('/api/v1/productos?sort=drop_table&direction=sideways&per_page=1000&page=0&include=hack&fields=hack&only_trashed=maybe')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort', 'direction', 'per_page', 'page', 'include.0', 'fields.0', 'only_trashed']);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_productos(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/productos')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/productos')->assertForbidden();
    }
}
