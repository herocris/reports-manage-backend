<?php

namespace App\Models;

use App\Models\Concerns\TracksCascadeDeletion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a formal audit report — the central entity of the application.
 *
 * An Informe owns several child collections that cascade-delete and restore
 * together with it:
 *  - `ubicaciones` (geographic locations)
 *  - `observaciones` (audit findings)
 *  - `bitacoras` (activity log entries)
 *  - `adjuntos` (file attachments via morph)
 *
 * Many-to-many relationships with {@see Persona} and {@see Bien} are managed
 * separately through `InformeController::attachPersona()` / `detachPersona()`
 * and `attachBien()` / `detachBien()`. Ambitos are synced via `InformeController::store()`
 * and `update()`.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $expediente Unique dossier or file reference number.
 * @property string $titulo Human-readable report title.
 * @property int $producto_id FK → productos.id — the audit product category.
 * @property Carbon $fecha_inicio Start date of the audit period.
 * @property Carbon $fecha_final End date of the audit period.
 * @property int $usuariocreador_id FK → users.id — the user who created this report.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Informe extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, TracksCascadeDeletion;

    public const array CASCADED_RELATIONS = ['ubicaciones', 'observaciones', 'bitacoras', 'adjuntos'];

    protected $fillable = [
        'expediente',
        'titulo',
        'producto_id',
        'fecha_inicio',
        'fecha_final',
        'usuariocreador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_final' => 'date',
        ];
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuariocreador_id');
    }

    /** @return BelongsToMany<Ambito, $this> */
    public function ambitos(): BelongsToMany
    {
        return $this->belongsToMany(Ambito::class, 'ambito_informe');
    }

    /** @return HasMany<Ubicacion, $this> */
    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class);
    }

    /** @return BelongsToMany<Persona, $this> */
    public function personas(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'informe_persona');
    }

    /** @return BelongsToMany<Bien, $this> */
    public function bienes(): BelongsToMany
    {
        return $this->belongsToMany(Bien::class, 'bien_informe');
    }

    /** @return HasMany<Observacion, $this> */
    public function observaciones(): HasMany
    {
        return $this->hasMany(Observacion::class);
    }

    /** @return HasMany<Bitacora, $this> */
    public function bitacoras(): HasMany
    {
        return $this->hasMany(Bitacora::class);
    }

    /** @return MorphMany<Adjunto, $this> */
    public function adjuntos(): MorphMany
    {
        return $this->morphMany(Adjunto::class, 'adjuntable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
