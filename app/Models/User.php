<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'institucion_id'])]
#[Hidden(['password', 'remember_token'])]
/**
 * Represents an authenticated system user.
 *
 * Users authenticate via Sanctum (bearer token for mobile clients or
 * session cookie for SPA clients). Roles and permissions are managed by
 * Spatie Permission with the `'sanctum'` guard. The `password` field
 * is automatically hashed via the `hashed` cast; `email` and `password`
 * are excluded from API responses by the {@see UserResource} field-level
 * security rules.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $name Full display name of the user.
 * @property string $email Unique email address used for login.
 * @property Carbon|null $email_verified_at Timestamp when the email was verified; null if unverified.
 * @property string $password Bcrypt-hashed password (never returned by API).
 * @property int|null $institucion_id FK → institucions.id — the employing institution, or null.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected string $guard_name = 'sanctum';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }

    /** @return BelongsTo<Institucion, $this> */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /** @return HasMany<Informe, $this> */
    public function informes(): HasMany
    {
        return $this->hasMany(Informe::class, 'usuariocreador_id');
    }

    /** @return HasMany<Observacion, $this> */
    public function observaciones(): HasMany
    {
        return $this->hasMany(Observacion::class, 'usuario_id');
    }

    /** @return HasMany<Observacion, $this> */
    public function observacionesCerradas(): HasMany
    {
        return $this->hasMany(Observacion::class, 'cerrada_por_id');
    }

    /** @return HasMany<ObservacionComentario, $this> */
    public function comentariosObservacion(): HasMany
    {
        return $this->hasMany(ObservacionComentario::class, 'usuario_id');
    }
}
