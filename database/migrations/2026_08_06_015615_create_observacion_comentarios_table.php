<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('observacion_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observacion_id')->constrained('observacions')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->text('contenido');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['observacion_id', 'created_at'], 'observacion_comentarios_fecha_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observacion_comentarios');
    }
};
