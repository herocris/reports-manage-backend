<?php

namespace App\Support\Query;

use App\Models\Adjunto;
use App\Models\Ambito;
use App\Models\Bien;
use App\Models\Bitacora;
use App\Models\Informe;
use App\Models\Institucion;
use App\Models\Observacion;
use App\Models\Permission;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Role;
use App\Models\TipoBien;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolves public activity-log subject aliases to stored morph classes.
 */
class ActivityLogSubjectTypes
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const MAP = [
        'Adjunto' => Adjunto::class,
        'Ambito' => Ambito::class,
        'Bien' => Bien::class,
        'Bitacora' => Bitacora::class,
        'Informe' => Informe::class,
        'Institucion' => Institucion::class,
        'Observacion' => Observacion::class,
        'Permission' => Permission::class,
        'Persona' => Persona::class,
        'Producto' => Producto::class,
        'Role' => Role::class,
        'TipoBien' => TipoBien::class,
        'Ubicacion' => Ubicacion::class,
        'User' => User::class,
    ];

    /**
     * Resolve a public alias to the fully qualified morph class stored in the database.
     *
     * @return class-string<Model>|null
     */
    public static function resolve(string $alias): ?string
    {
        return self::MAP[$alias] ?? null;
    }
}
