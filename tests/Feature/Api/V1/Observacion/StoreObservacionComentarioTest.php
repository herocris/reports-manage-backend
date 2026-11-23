<?php

namespace Tests\Feature\Api\V1\Observacion;

use App\Models\Informe;
use App\Models\Observacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

class StoreObservacionComentarioTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_informe_creator_can_comment_without_closing(): void
    {
        $responsable = $this->createAuthorizedUser(['observaciones.update']);
        $informe = Informe::factory()->create(['usuariocreador_id' => $responsable->id]);
        $observacion = Observacion::factory()->create(['informe_id' => $informe->id]);

        $this->actingAs($responsable)
            ->postJson("/api/v1/observaciones/{$observacion->id}/comentarios", [
                'contenido' => 'Estoy revisando este requerimiento.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.usuario_id', $responsable->id)
            ->assertJsonPath('data.contenido', 'Estoy revisando este requerimiento.');

        $this->assertSame('abierta', $observacion->fresh()->estado->value);
    }

    public function test_another_user_cannot_comment(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.update']);
        $observacion = Observacion::factory()->create();

        $this->actingAs($actor)
            ->postJson("/api/v1/observaciones/{$observacion->id}/comentarios", ['contenido' => 'Comentario.'])
            ->assertForbidden();
    }
}
