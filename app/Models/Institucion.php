<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Represents an organization or governmental body that employs users.
 *
 * Users are linked to an Institucion via `institucion_id` on the `users` table.
 * Informes may also be associated with an institution through their creating user.
 * Soft-deleting an Institucion does not cascade to users.
 *
 * @property int              $id        Auto-incrementing primary key.
 * @property string           $nombre    Full legal or commercial name of the institution.
 * @property string           $contacto  Name of the primary contact person.
 * @property string           $telefono  Contact telephone number.
 * @property \Carbon\Carbon|null $deleted_at  Timestamp of soft deletion; null when active.
 * @property \Carbon\Carbon   $created_at  Timestamp of record creation.
 * @property \Carbon\Carbon   $updated_at  Timestamp of last modification.
 */
class Institucion extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
    ];

    /** @return HasMany<User, $this> */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
