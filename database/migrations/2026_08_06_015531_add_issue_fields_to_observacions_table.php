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
        Schema::table('observacions', function (Blueprint $table) {
            $table->unsignedBigInteger('numero')->nullable()->after('id');
            $table->string('titulo')->nullable()->after('numero');
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta')->index()->after('descripcion');
            $table->timestamp('cerrada_at')->nullable()->after('estado');
            $table->foreignId('cerrada_por_id')
                ->nullable()
                ->after('cerrada_at')
                ->constrained('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('observacions', function (Blueprint $table) {
            $table->dropForeign(['cerrada_por_id']);
            $table->dropIndex(['estado']);
            $table->dropColumn(['numero', 'titulo', 'estado', 'cerrada_at', 'cerrada_por_id']);
        });
    }
};
