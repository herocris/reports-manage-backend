<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ambitos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tipo_biens', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('institucions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ubicacions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('ubicacions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('institucions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tipo_biens', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('ambitos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
