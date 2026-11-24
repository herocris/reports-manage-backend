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
 * Represents a chronological log entry tied to a specific Informe.
 *
 * Each Bitacora records a descriptive narrative and the calendar date on which
 * the event occurred. Bitacoras participate in cascade soft-delete with their
 * parent {@see Informe}; they are soft-deleted and restored together.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $descripcion Narrative of the logged event or activity.
 * @property Carbon $fecha Calendar date on which the event occurred.
 * @property int $informe_id FK → informes.id — the parent report.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Bitacora extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, TracksCascadeDeletion;

    protected $fillable = [
        'descripcion',
        'fecha',
        'informe_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

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
