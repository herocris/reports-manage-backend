<?php

namespace Tests\Feature\Api\V1\Observacion;

use App\Enums\ObservacionEstado;
use App\Models\Informe;
use App\Models\Observacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

class CloseObservacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_informe_creator_can_close_observation_with_a_comment(): void
    {
        $responsable = $this->createAuthorizedUser(['observaciones.update']);
        $informe = Informe::factory()->create(['usuariocreador_id' => $responsable->id]);
        $observacion = Observacion::factory()->create(['informe_id' => $informe->id]);

        $this->actingAs($responsable)
            ->postJson("/api/v1/observaciones/{$observacion->id}/cerrar", [
                'contenido' => 'Se corrigieron y verificaron las conclusiones.',
            ])
            ->assertOk()
            ->assertJsonPath('data.estado', ObservacionEstado::Cerrada->value)
            ->assertJsonPath('data.cerrada_por_id', $responsable->id)
            ->assertJsonCount(1, 'data.comentarios');

        $this->assertDatabaseHas('observacion_comentarios', [
            'observacion_id' => $observacion->id,
            'usuario_id' => $responsable->id,
            'contenido' => 'Se corrigieron y verificaron las conclusiones.',
        ]);
    }

    public function test_only_informe_creator_can_close_observation(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.update']);
        $observacion = Observacion::factory()->create();

        $this->actingAs($actor)
            ->postJson("/api/v1/observaciones/{$observacion->id}/cerrar", ['contenido' => 'Corregido.'])
            ->assertForbidden();
    }

    public function test_closing_requires_comment_and_cannot_be_repeated(): void
    {
        $responsable = $this->createAuthorizedUser(['observaciones.update']);
        $informe = Informe::factory()->create(['usuariocreador_id' => $responsable->id]);
        $observacion = Observacion::factory()->create(['informe_id' => $informe->id]);

        $this->actingAs($responsable)
            ->postJson("/api/v1/observaciones/{$observacion->id}/cerrar", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('contenido');

        $this->actingAs($responsable)
            ->postJson("/api/v1/observaciones/{$observacion->id}/cerrar", ['contenido' => 'Corregido.'])
            ->assertOk();

        $this->actingAs($responsable)
            ->postJson("/api/v1/observaciones/{$observacion->id}/cerrar", ['contenido' => 'Otra vez.'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }
}
