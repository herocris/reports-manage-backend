<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents an audit product or work-output category that groups Informes.
 *
 * A Producto is a high-level classification (e.g. "Auditoría Financiera") used
 * to categorize audit reports. Each {@see Informe} references exactly one Producto.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $nombre Short product name (e.g. "Auditoría Financiera").
 * @property string $descripcion Detailed description of the audit product type.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Producto extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /** @return HasMany<Informe, $this> */
    public function informes(): HasMany
    {
        return $this->hasMany(Informe::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
