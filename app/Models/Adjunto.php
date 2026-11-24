<?php

namespace App\Models;

use App\Models\Concerns\TracksCascadeDeletion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a polymorphic file attachment stored on the private `attachments` disk.
 *
 * An Adjunto can belong to an {@see Informe}, {@see Persona}, or {@see Bien}
 * via the `adjuntable` morphic relation. Files are stored under
 * `adjuntos/{tipo}/` and the `url` column holds the relative disk path.
 * When force-deleted, the physical file is removed by the boot hook;
 * soft-delete only sets `deleted_at` and retains the file.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $url Relative path on the private disk (e.g. `adjuntos/informe/file.jpg`).
 * @property string $descripcion Human-readable name / label for the file.
 * @property int $adjuntable_id FK to the polymorphic owner record.
 * @property string $adjuntable_type Fully-qualified class name of the polymorphic owner.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Adjunto extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, TracksCascadeDeletion;

    protected $fillable = [
        'url',
        'descripcion',
        'adjuntable_id',
        'adjuntable_type',
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (Adjunto $adjunto): void {
            Storage::disk('attachments')->delete($adjunto->url);
        });
    }

    /** @return MorphTo<Model, $this> */
    public function adjuntable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
