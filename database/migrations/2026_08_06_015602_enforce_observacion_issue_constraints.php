<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('observacions', function (Blueprint $table) {
            $table->unsignedBigInteger('numero')->nullable(false)->change();
            $table->string('titulo')->nullable(false)->change();
            $table->unique(['informe_id', 'numero'], 'observacions_informe_numero_unique');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE observacions
            ADD CONSTRAINT observacions_estado_cierre_check CHECK (
                (estado = 'abierta' AND cerrada_at IS NULL AND cerrada_por_id IS NULL)
                OR
                (estado = 'cerrada' AND cerrada_at IS NOT NULL AND cerrada_por_id IS NOT NULL)
            )
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE observacions DROP CHECK observacions_estado_cierre_check');

        Schema::table('observacions', function (Blueprint $table) {
            $table->dropUnique('observacions_informe_numero_unique');
            $table->unsignedBigInteger('numero')->nullable()->change();
            $table->string('titulo')->nullable()->change();
        });
    }
};
