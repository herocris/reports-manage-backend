<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a category or type for Bien (asset) records.
 *
 * TipoBien is used to classify bienes (e.g. "Inmueble", "Vehículo",
 * "Equipo"). A TipoBien can have many {@see Bien} records referencing it.
 * Soft-deleting a TipoBien does not cascade to existing bienes.
 *
 * @property int              $id           Auto-incrementing primary key.
 * @property string           $nombre       Short category name (e.g. "Inmueble").
 * @property string           $descripcion  Detailed description of the asset type.
 * @property \Carbon\Carbon|null $deleted_at  Timestamp of soft deletion; null when active.
 * @property \Carbon\Carbon   $created_at   Timestamp of record creation.
 * @property \Carbon\Carbon   $updated_at   Timestamp of last modification.
 */
class TipoBien extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /** @return HasMany<Bien, $this> */
    public function bienes(): HasMany
    {
        return $this->hasMany(Bien::class, 'tipobien_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
