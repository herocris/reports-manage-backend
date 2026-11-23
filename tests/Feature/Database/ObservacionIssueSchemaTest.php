<?php

namespace Tests\Feature\Database;

use App\Models\Informe;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ObservacionIssueSchemaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_observaciones_and_comments_have_the_issue_fields(): void
    {
        $this->assertTrue(Schema::hasColumns('observacions', [
            'numero',
            'titulo',
            'estado',
            'cerrada_at',
            'cerrada_por_id',
        ]));

        $this->assertTrue(Schema::hasColumns('observacion_comentarios', [
            'observacion_id',
            'usuario_id',
            'contenido',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_an_observation_accepts_comments(): void
    {
        $user = User::factory()->create();
        $informe = Informe::factory()->create();
        $now = now();

        $observacionId = DB::table('observacions')->insertGetId([
            'numero' => 1,
            'titulo' => 'Corregir conclusiones',
            'descripcion' => 'Las conclusiones necesitan mayor detalle.',
            'estado' => 'abierta',
            'usuario_id' => $user->id,
            'informe_id' => $informe->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('observacion_comentarios')->insert([
            'observacion_id' => $observacionId,
            'usuario_id' => $user->id,
            'contenido' => 'Se debe incluir la fuente de los datos.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->assertDatabaseHas('observacion_comentarios', [
            'observacion_id' => $observacionId,
            'usuario_id' => $user->id,
            'contenido' => 'Se debe incluir la fuente de los datos.',
        ]);
    }

    public function test_observation_numbers_are_unique_per_informe(): void
    {
        $user = User::factory()->create();
        $informe = Informe::factory()->create();
        $now = now();

        $attributes = [
            'numero' => 1,
            'titulo' => 'Primera observación',
            'descripcion' => 'Descripción de la observación.',
            'estado' => 'abierta',
            'usuario_id' => $user->id,
            'informe_id' => $informe->id,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('observacions')->insert($attributes);

        $this->expectException(QueryException::class);

        DB::table('observacions')->insert($attributes);
    }

    public function test_open_observation_cannot_have_closing_metadata(): void
    {
        $user = User::factory()->create();
        $informe = Informe::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('observacions')->insert([
            'numero' => 1,
            'titulo' => 'Estado inconsistente',
            'descripcion' => 'No debe poder cerrarse parcialmente.',
            'estado' => 'abierta',
            'cerrada_at' => now(),
            'cerrada_por_id' => $user->id,
            'usuario_id' => $user->id,
            'informe_id' => $informe->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
