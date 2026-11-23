<?php

namespace App\Models;

use App\Enums\ObservacionEstado;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents a finding, comment, or remark recorded during an audit.
 *
 * An Observacion belongs to both an {@see Informe} and a {@see User} (the author).
 * It participates in cascade soft-delete with its parent Informe.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $descripcion The text of the observation or finding.
 * @property int $usuario_id FK → users.id — the user who recorded this observation.
 * @property int $informe_id FK → informes.id — the parent report.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Observacion extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'numero',
        'titulo',
        'descripcion',
        'usuario_id',
        'informe_id',
        'estado',
        'cerrada_at',
        'cerrada_por_id',
    ];

    protected $attributes = ['estado' => 'abierta'];

    protected function casts(): array
    {
        return [
            'estado' => ObservacionEstado::class,
            'cerrada_at' => 'datetime',
        ];
    }

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

    /** @return BelongsTo<User, $this> */
    public function cerradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por_id');
    }

    /** @return HasMany<ObservacionComentario, $this> */
    public function comentarios(): HasMany
    {
        return $this->hasMany(ObservacionComentario::class)->oldest();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
