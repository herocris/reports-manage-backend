<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a thematic or geographic scope used to classify Informes.
 *
 * An Ambito is linked to informes via the `ambito_informe` many-to-many pivot.
 * Soft-deleting an Ambito does not cascade; existing pivot rows are retained.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $nombre Short name identifying the scope (e.g. "Regional").
 * @property string $descripcion Extended explanation of the scope's purpose.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Ambito extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /** @return BelongsToMany<Informe, $this> */
    public function informes(): BelongsToMany
    {
        return $this->belongsToMany(Informe::class, 'ambito_informe');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
