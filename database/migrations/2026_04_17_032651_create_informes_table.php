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
        Schema::create('informes', function (Blueprint $table) {
            $table->id();
            $table->string('expediente')->unique();
            $table->string('titulo');
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_final');
            $table->foreignId('usuariocreador_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ambito_informe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambito_id')->constrained()->cascadeOnDelete();
            $table->foreignId('informe_id')->constrained()->cascadeOnDelete();
            $table->unique(['ambito_id', 'informe_id']);
        });

        Schema::create('informe_persona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained()->cascadeOnDelete();
            $table->unique(['informe_id', 'persona_id']);
        });

        Schema::create('bien_informe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained()->cascadeOnDelete();
            $table->foreignId('informe_id')->constrained()->cascadeOnDelete();
            $table->unique(['bien_id', 'informe_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bien_informe');
        Schema::dropIfExists('informe_persona');
        Schema::dropIfExists('ambito_informe');
        Schema::dropIfExists('informes');
    }
};
