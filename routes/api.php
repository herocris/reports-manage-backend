<?php

/**
 * API routes for version 1.
 *
 * All authenticated routes require the `auth:sanctum` middleware and are
 * throttled by the `api` rate limiter (60 req/min). Permission checks are
 * enforced per route via the `permission:{name}` middleware.
 *
 * @see \App\Http\Controllers\Api\V1\AuthController
 */
use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\AdjuntoController;
use App\Http\Controllers\Api\V1\AmbitoController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BienController;
use App\Http\Controllers\Api\V1\BitacoraController;
use App\Http\Controllers\Api\V1\InstitucionController;
use App\Http\Controllers\Api\V1\InformeController;
use App\Http\Controllers\Api\V1\ObservacionController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PersonaController;
use App\Http\Controllers\Api\V1\ProductoController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TipoBienController;
use App\Http\Controllers\Api\V1\UbicacionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('v1.')->group(function (): void {
    Route::post('auth/web/login', [AuthController::class, 'loginWeb'])
        ->middleware('throttle:login')
        ->name('auth.web.login');

    Route::post('auth/mobile/login', [AuthController::class, 'loginMobile'])
        ->middleware('throttle:login')
        ->name('auth.mobile.login');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('auth.login');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::post('auth/web/logout', [AuthController::class, 'logoutWeb'])->name('auth.web.logout');
        Route::post('auth/mobile/logout', [AuthController::class, 'logoutMobile'])->name('auth.mobile.logout');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        Route::match(['put', 'patch'], 'me', [AuthController::class, 'updateProfile'])->name('auth.me.update');

        Route::prefix('users')->as('users.')->group(function (): void {
            Route::get('/', [UserController::class, 'index'])->middleware('permission:users.view')->name('index');
            Route::post('/', [UserController::class, 'store'])->middleware('permission:users.create')->name('store');
            Route::post('{user}/restore', [UserController::class, 'restore'])->middleware('permission:users.restore')->name('restore')->withTrashed();
            Route::get('{user}', [UserController::class, 'show'])->middleware('permission:users.view')->name('show');
            Route::match(['put', 'patch'], '{user}', [UserController::class, 'update'])->middleware('permission:users.update')->name('update');
            Route::delete('{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('destroy');
        });

        Route::prefix('roles')->as('roles.')->group(function (): void {
            Route::get('/', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('index');
            Route::post('/', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('store');
            Route::post('{role}/restore', [RoleController::class, 'restore'])->middleware('permission:roles.restore')->name('restore')->withTrashed();
            Route::get('{role}', [RoleController::class, 'show'])->middleware('permission:roles.view')->name('show');
            Route::match(['put', 'patch'], '{role}', [RoleController::class, 'update'])->middleware('permission:roles.update')->name('update');
            Route::delete('{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('destroy');
        });

        Route::prefix('permissions')->as('permissions.')->group(function (): void {
            Route::get('/', [PermissionController::class, 'index'])->middleware('permission:permissions.view')->name('index');
            Route::post('/', [PermissionController::class, 'store'])->middleware('permission:permissions.create')->name('store');
            Route::post('{permission}/restore', [PermissionController::class, 'restore'])->middleware('permission:permissions.restore')->name('restore')->withTrashed();
            Route::get('{permission}', [PermissionController::class, 'show'])->middleware('permission:permissions.view')->name('show');
            Route::match(['put', 'patch'], '{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.update')->name('update');
            Route::delete('{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete')->name('destroy');
        });

        Route::prefix('ambitos')->as('ambitos.')->group(function (): void {
            Route::get('/', [AmbitoController::class, 'index'])->middleware('permission:ambitos.view')->name('index');
            Route::post('/', [AmbitoController::class, 'store'])->middleware('permission:ambitos.create')->name('store');
            Route::post('{ambito}/restore', [AmbitoController::class, 'restore'])->middleware('permission:ambitos.restore')->name('restore')->withTrashed();
            Route::get('{ambito}', [AmbitoController::class, 'show'])->middleware('permission:ambitos.view')->name('show');
            Route::match(['put', 'patch'], '{ambito}', [AmbitoController::class, 'update'])->middleware('permission:ambitos.update')->name('update');
            Route::delete('{ambito}', [AmbitoController::class, 'destroy'])->middleware('permission:ambitos.delete')->name('destroy');
        });

        Route::prefix('tipos-bien')->as('tipos-bien.')->group(function (): void {
            Route::get('/', [TipoBienController::class, 'index'])->middleware('permission:tipos_bien.view')->name('index');
            Route::post('/', [TipoBienController::class, 'store'])->middleware('permission:tipos_bien.create')->name('store');
            Route::post('{tipoBien}/restore', [TipoBienController::class, 'restore'])->middleware('permission:tipos_bien.restore')->name('restore')->withTrashed();
            Route::get('{tipoBien}', [TipoBienController::class, 'show'])->middleware('permission:tipos_bien.view')->name('show');
            Route::match(['put', 'patch'], '{tipoBien}', [TipoBienController::class, 'update'])->middleware('permission:tipos_bien.update')->name('update');
            Route::delete('{tipoBien}', [TipoBienController::class, 'destroy'])->middleware('permission:tipos_bien.delete')->name('destroy');
        });

        Route::prefix('productos')->as('productos.')->group(function (): void {
            Route::get('/', [ProductoController::class, 'index'])->middleware('permission:productos.view')->name('index');
            Route::post('/', [ProductoController::class, 'store'])->middleware('permission:productos.create')->name('store');
            Route::post('{producto}/restore', [ProductoController::class, 'restore'])->middleware('permission:productos.restore')->name('restore')->withTrashed();
            Route::get('{producto}', [ProductoController::class, 'show'])->middleware('permission:productos.view')->name('show');
            Route::match(['put', 'patch'], '{producto}', [ProductoController::class, 'update'])->middleware('permission:productos.update')->name('update');
            Route::delete('{producto}', [ProductoController::class, 'destroy'])->middleware('permission:productos.delete')->name('destroy');
        });

        Route::prefix('personas')->as('personas.')->group(function (): void {
            Route::get('/', [PersonaController::class, 'index'])->middleware('permission:personas.view')->name('index');
            Route::post('/', [PersonaController::class, 'store'])->middleware('permission:personas.create')->name('store');
            Route::post('{persona}/restore', [PersonaController::class, 'restore'])->middleware('permission:personas.restore')->name('restore')->withTrashed();
            Route::get('{persona}', [PersonaController::class, 'show'])->middleware('permission:personas.view')->name('show');
            Route::match(['put', 'patch'], '{persona}', [PersonaController::class, 'update'])->middleware('permission:personas.update')->name('update');
            Route::delete('{persona}', [PersonaController::class, 'destroy'])->middleware('permission:personas.delete')->name('destroy');
        });

        Route::prefix('instituciones')->as('instituciones.')->group(function (): void {
            Route::get('/', [InstitucionController::class, 'index'])->middleware('permission:instituciones.view')->name('index');
            Route::post('/', [InstitucionController::class, 'store'])->middleware('permission:instituciones.create')->name('store');
            Route::post('{institucion}/restore', [InstitucionController::class, 'restore'])->middleware('permission:instituciones.restore')->name('restore')->withTrashed();
            Route::get('{institucion}', [InstitucionController::class, 'show'])->middleware('permission:instituciones.view')->name('show');
            Route::match(['put', 'patch'], '{institucion}', [InstitucionController::class, 'update'])->middleware('permission:instituciones.update')->name('update');
            Route::delete('{institucion}', [InstitucionController::class, 'destroy'])->middleware('permission:instituciones.delete')->name('destroy');
        });

        Route::prefix('ubicaciones')->as('ubicaciones.')->group(function (): void {
            Route::get('/', [UbicacionController::class, 'index'])->middleware('permission:ubicaciones.view')->name('index');
            Route::post('/', [UbicacionController::class, 'store'])->middleware('permission:ubicaciones.create')->name('store');
            Route::post('{ubicacion}/restore', [UbicacionController::class, 'restore'])->middleware('permission:ubicaciones.restore')->name('restore')->withTrashed();
            Route::get('{ubicacion}', [UbicacionController::class, 'show'])->middleware('permission:ubicaciones.view')->name('show');
            Route::match(['put', 'patch'], '{ubicacion}', [UbicacionController::class, 'update'])->middleware('permission:ubicaciones.update')->name('update');
            Route::delete('{ubicacion}', [UbicacionController::class, 'destroy'])->middleware('permission:ubicaciones.delete')->name('destroy');
        });

        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->middleware('permission:activity-log.view')
            ->name('activity-logs.index');

        Route::prefix('bienes')->as('bienes.')->group(function (): void {
            Route::get('/', [BienController::class, 'index'])->middleware('permission:bienes.view')->name('index');
            Route::post('/', [BienController::class, 'store'])->middleware('permission:bienes.create')->name('store');
            Route::post('{bien}/restore', [BienController::class, 'restore'])->middleware('permission:bienes.restore')->name('restore')->withTrashed();
            Route::get('{bien}', [BienController::class, 'show'])->middleware('permission:bienes.view')->name('show');
            Route::match(['put', 'patch'], '{bien}', [BienController::class, 'update'])->middleware('permission:bienes.update')->name('update');
            Route::delete('{bien}', [BienController::class, 'destroy'])->middleware('permission:bienes.delete')->name('destroy');
        });

        Route::prefix('informes')->as('informes.')->group(function (): void {
            Route::get('/', [InformeController::class, 'index'])->middleware('permission:informes.view')->name('index');
            Route::post('/', [InformeController::class, 'store'])->middleware('permission:informes.create')->name('store');
            Route::post('{informe}/restore', [InformeController::class, 'restore'])->middleware('permission:informes.restore')->name('restore')->withTrashed();
            Route::get('{informe}/edit', [InformeController::class, 'edit'])->middleware('permission:informes.view')->name('edit');
            Route::post('{informe}/personas', [InformeController::class, 'attachPersona'])->middleware('permission:informes.update')->name('personas.attach');
            Route::delete('{informe}/personas/{persona}', [InformeController::class, 'detachPersona'])->middleware('permission:informes.update')->name('personas.detach');
            Route::post('{informe}/bienes', [InformeController::class, 'attachBien'])->middleware('permission:informes.update')->name('bienes.attach');
            Route::delete('{informe}/bienes/{bien}', [InformeController::class, 'detachBien'])->middleware('permission:informes.update')->name('bienes.detach');
            Route::get('{informe}', [InformeController::class, 'show'])->middleware('permission:informes.view')->name('show');
            Route::match(['put', 'patch'], '{informe}', [InformeController::class, 'update'])->middleware('permission:informes.update')->name('update');
            Route::delete('{informe}', [InformeController::class, 'destroy'])->middleware('permission:informes.delete')->name('destroy');
        });

        Route::prefix('observaciones')->as('observaciones.')->group(function (): void {
            Route::get('/', [ObservacionController::class, 'index'])->middleware('permission:observaciones.view')->name('index');
            Route::post('/', [ObservacionController::class, 'store'])->middleware('permission:observaciones.create')->name('store');
            Route::post('{observacion}/restore', [ObservacionController::class, 'restore'])->middleware('permission:observaciones.restore')->name('restore')->withTrashed();
            Route::get('{observacion}', [ObservacionController::class, 'show'])->middleware('permission:observaciones.view')->name('show');
            Route::match(['put', 'patch'], '{observacion}', [ObservacionController::class, 'update'])->middleware('permission:observaciones.update')->name('update');
            Route::delete('{observacion}', [ObservacionController::class, 'destroy'])->middleware('permission:observaciones.delete')->name('destroy');
        });

        Route::prefix('bitacoras')->as('bitacoras.')->group(function (): void {
            Route::get('/', [BitacoraController::class, 'index'])->middleware('permission:bitacoras.view')->name('index');
            Route::post('/', [BitacoraController::class, 'store'])->middleware('permission:bitacoras.create')->name('store');
            Route::post('{bitacora}/restore', [BitacoraController::class, 'restore'])->middleware('permission:bitacoras.restore')->name('restore')->withTrashed();
            Route::get('{bitacora}', [BitacoraController::class, 'show'])->middleware('permission:bitacoras.view')->name('show');
            Route::match(['put', 'patch'], '{bitacora}', [BitacoraController::class, 'update'])->middleware('permission:bitacoras.update')->name('update');
            Route::delete('{bitacora}', [BitacoraController::class, 'destroy'])->middleware('permission:bitacoras.delete')->name('destroy');
        });

        Route::prefix('adjuntos')->as('adjuntos.')->group(function (): void {
            Route::get('/', [AdjuntoController::class, 'index'])->middleware('permission:adjuntos.view')->name('index');
            Route::post('/', [AdjuntoController::class, 'store'])->middleware('permission:adjuntos.create')->name('store');
            Route::post('{adjunto}/restore', [AdjuntoController::class, 'restore'])->middleware('permission:adjuntos.restore')->name('restore')->withTrashed();
            Route::get('{adjunto}/download', [AdjuntoController::class, 'download'])->middleware('permission:adjuntos.view')->name('download');
            Route::get('{adjunto}/stream', [AdjuntoController::class, 'stream'])->middleware('permission:adjuntos.view')->name('stream');
            Route::get('{adjunto}', [AdjuntoController::class, 'show'])->middleware('permission:adjuntos.view')->name('show');
            Route::match(['put', 'patch'], '{adjunto}', [AdjuntoController::class, 'update'])->middleware('permission:adjuntos.update')->name('update');
            Route::delete('{adjunto}', [AdjuntoController::class, 'destroy'])->middleware('permission:adjuntos.delete')->name('destroy');
        });
    });
});
