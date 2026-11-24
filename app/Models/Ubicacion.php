<?php

namespace App\Models;

use App\Models\Concerns\TracksCascadeDeletion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a geographic location or address relevant to an Informe.
 *
 * A Ubicacion records GPS coordinates (`latitud`, `longitud`) and a textual
 * description of the location. It belongs to an {@see Informe} via `informe_id`
 * and participates in cascade soft-delete with its parent Informe.
 *
 * @property int $id Auto-incrementing primary key.
 * @property float $latitud Latitude of the location in decimal degrees.
 * @property float $longitud Longitude of the location in decimal degrees.
 * @property string $descripcion Human-readable address or place name.
 * @property int $informe_id FK → informes.id — the parent report.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Ubicacion extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, TracksCascadeDeletion;

    protected $fillable = [
        'latitud',
        'longitud',
        'descripcion',
        'informe_id',
    ];

    /** @return BelongsTo<Informe, $this> */
    public function informe(): BelongsTo
    {
        return $this->belongsTo(Informe::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
