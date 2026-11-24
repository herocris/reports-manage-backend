<?php

namespace Tests\Feature\Api\V1\Informe;

use App\Models\Adjunto;
use App\Models\Ambito;
use App\Models\Bien;
use App\Models\Bitacora;
use App\Models\Informe;
use App\Models\Observacion;
use App\Models\Persona;
use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving and editing informe records,
 * including the `edit` endpoint that eager-loads all associations.
 */
class ShowEditInformeTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view', 'informes.update', 'informes.delete']);
        $informe = Informe::factory()->create();

        $this->actingAs($actor)->getJson("/api/v1/informes/{$informe->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/informes/{$informe->id}", ['titulo' => 'Actualizado'])
            ->assertOk()->assertJsonFragment(['titulo' => 'Actualizado']);
        $this->actingAs($actor)->deleteJson("/api/v1/informes/{$informe->id}")->assertOk();

        $this->assertSoftDeleted($informe);
    }

    public function test_show_returns_404_for_nonexistent_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);

        $this->actingAs($actor)->getJson('/api/v1/informes/999999')->assertNotFound();
    }

    public function test_authenticated_user_can_get_edit_data_with_all_related_resources(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $informe = Informe::factory()->create();

        $persona = Persona::factory()->create();
        $bien = Bien::factory()->create();
        $ambito = Ambito::factory()->create();

        $informe->personas()->attach($persona->id);
        $informe->bienes()->attach($bien->id);
        $informe->ambitos()->attach($ambito->id);

        Ubicacion::factory()->create(['informe_id' => $informe->id]);
        Observacion::factory()->create(['informe_id' => $informe->id]);
        Bitacora::factory()->create(['informe_id' => $informe->id]);
        Adjunto::factory()->create([
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)->getJson("/api/v1/informes/{$informe->id}/edit")
            ->assertOk()
            ->assertJsonPath('data.id', $informe->id)
            ->assertJsonPath('data.producto_id', $informe->producto_id)
            ->assertJsonPath('data.producto.id', $informe->producto_id)
            ->assertJsonCount(1, 'data.personas')
            ->assertJsonCount(1, 'data.bienes')
            ->assertJsonCount(1, 'data.adjuntos')
            ->assertJsonCount(1, 'data.ubicaciones')
            ->assertJsonCount(1, 'data.observaciones')
            ->assertJsonCount(1, 'data.bitacoras')
            ->assertJsonCount(1, 'data.ambitos');
    }
}
