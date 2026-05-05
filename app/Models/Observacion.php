<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a finding, comment, or remark recorded during an audit.
 *
 * An Observacion belongs to both an {@see Informe} and a {@see User} (the author).
 * It participates in cascade soft-delete with its parent Informe.
 *
 * @property int              $id           Auto-incrementing primary key.
 * @property string           $descripcion  The text of the observation or finding.
 * @property int              $usuario_id   FK → users.id — the user who recorded this observation.
 * @property int              $informe_id   FK → informes.id — the parent report.
 * @property \Carbon\Carbon|null $deleted_at  Timestamp of soft deletion; null when active.
 * @property \Carbon\Carbon   $created_at   Timestamp of record creation.
 * @property \Carbon\Carbon   $updated_at   Timestamp of last modification.
 */
class Observacion extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'descripcion',
        'usuario_id',
        'informe_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
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
