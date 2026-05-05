<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a polymorphic file attachment stored on the `public` disk.
 *
 * An Adjunto can belong to an {@see Informe}, {@see Persona}, or {@see Bien}
 * via the `adjuntable` morphic relation. Files are stored under
 * `adjuntos/{tipo}/` and the `url` column holds the relative disk path.
 * When force-deleted, the physical file is removed by the boot hook;
 * soft-delete only sets `deleted_at` and retains the file.
 *
 * @property int              $id               Auto-incrementing primary key.
 * @property string           $url              Relative path on the `public` disk (e.g. `adjuntos/informe/file.pdf`).
 * @property string           $descripcion      Human-readable name / label for the file.
 * @property int              $adjuntable_id    FK to the polymorphic owner record.
 * @property string           $adjuntable_type  Fully-qualified class name of the polymorphic owner.
 * @property \Carbon\Carbon|null $deleted_at   Timestamp of soft deletion; null when active.
 * @property \Carbon\Carbon   $created_at       Timestamp of record creation.
 * @property \Carbon\Carbon   $updated_at       Timestamp of last modification.
 */
class Adjunto extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'url',
        'descripcion',
        'adjuntable_id',
        'adjuntable_type',
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (Adjunto $adjunto): void {
            Storage::disk('public')->delete($adjunto->url);
        });
    }

    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, $this> */
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
