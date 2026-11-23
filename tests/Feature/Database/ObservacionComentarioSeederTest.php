<?php

namespace Tests\Feature\Database;

use App\Models\Informe;
use App\Models\Observacion;
use App\Models\ObservacionComentario;
use App\Models\User;
use Database\Seeders\ObservacionComentarioSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ObservacionComentarioSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_creates_comments_with_the_informe_creator_without_duplicating_them(): void
    {
        $responsable = User::factory()->create();
        $informe = Informe::factory()->create(['usuariocreador_id' => $responsable->id]);
        $observaciones = collect([
            Observacion::factory()->create(['informe_id' => $informe->id]),
            Observacion::factory()->create(['informe_id' => $informe->id]),
        ]);

        $this->seed(ObservacionComentarioSeeder::class);

        $comentarios = ObservacionComentario::query()->get();

        $this->assertGreaterThanOrEqual(2, $comentarios->count());
        $this->assertLessThanOrEqual(6, $comentarios->count());
        $this->assertEqualsCanonicalizing(
            $observaciones->pluck('id')->all(),
            $comentarios->pluck('observacion_id')->unique()->all(),
        );
        $this->assertTrue($comentarios->every(
            fn (ObservacionComentario $comentario): bool => $comentario->usuario_id === $responsable->id,
        ));

        $this->seed(ObservacionComentarioSeeder::class);

        $this->assertSame($comentarios->count(), ObservacionComentario::query()->count());
    }
}
