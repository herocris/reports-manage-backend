<?php

namespace Tests\Feature\Api\V1\Informe;

use App\Models\Informe;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering informe records.
 */
class IndexInformeTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_informes(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $recent = now()->startOfDay();
        $firstRecent = Informe::factory()->create(['created_at' => $recent]);
        $older = Informe::factory()->create(['created_at' => $recent->copy()->subDay()]);
        $lastRecent = Informe::factory()->create(['created_at' => $recent]);

        $this->actingAs($actor)->getJson('/api/v1/informes')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $lastRecent->id)
            ->assertJsonPath('data.1.id', $firstRecent->id)
            ->assertJsonPath('data.2.id', $older->id);
    }

    public function test_authenticated_user_can_filter_informes_by_producto_name(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $matchingProduct = Producto::factory()->create(['nombre' => 'Producto Saepe Especial']);
        $otherProduct = Producto::factory()->create(['nombre' => 'Producto Distinto']);

        $matchingInforme = Informe::factory()->create(['producto_id' => $matchingProduct->id]);
        Informe::factory()->create(['producto_id' => $otherProduct->id]);

        $this->actingAs($actor)
            ->getJson('/api/v1/informes?filter[producto]=saepe')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingInforme->id);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_informes(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/informes')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/informes')->assertForbidden();
    }
}
