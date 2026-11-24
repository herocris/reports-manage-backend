<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a natural person (individual) involved in an audit.
 *
 * A Persona is linked to one or more {@see Informe} records through the
 * `informe_persona` pivot table. It also has polymorphic {@see Adjunto}
 * file attachments via the `adjuntos()` morph relation.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $nombre Full name of the person.
 * @property string $numero_identificacion National ID, passport, or similar identifier.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Persona extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'nombre',
        'numero_identificacion',
    ];

    /** @return BelongsToMany<Informe, $this> */
    public function informes(): BelongsToMany
    {
        return $this->belongsToMany(Informe::class, 'informe_persona');
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
