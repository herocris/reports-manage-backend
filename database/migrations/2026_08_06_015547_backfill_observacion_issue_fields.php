<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE observacions AS observacion
            INNER JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY informe_id ORDER BY id) AS numero
                FROM observacions
            ) AS numeradas ON numeradas.id = observacion.id
            SET observacion.numero = numeradas.numero,
                observacion.titulo = LEFT(observacion.descripcion, 255)
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('observacions')->update([
            'numero' => null,
            'titulo' => null,
        ]);
    }
};
