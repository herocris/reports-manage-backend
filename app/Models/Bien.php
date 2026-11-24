<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a physical or intangible asset subject to audit.
 *
 * A Bien belongs to a {@see TipoBien} category and is linked to one or more
 * {@see Informe} records via the `bien_informe` pivot table. It also
 * has polymorphic {@see Adjunto} file attachments via the `adjuntos()` relation.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $descripcion Description identifying the asset.
 * @property int $tipobien_id FK → tipo_biens.id — the category this asset belongs to.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Bien extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'descripcion',
        'tipobien_id',
    ];

    /** @return BelongsTo<TipoBien, $this> */
    public function tipoBien(): BelongsTo
    {
        return $this->belongsTo(TipoBien::class, 'tipobien_id');
    }

    /** @return BelongsToMany<Informe, $this> */
    public function informes(): BelongsToMany
    {
        return $this->belongsToMany(Informe::class, 'bien_informe');
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
