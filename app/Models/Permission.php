<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Custom Permission model extending Spatie Permission's Permission.
 *
 * Overrides the default guard to `'sanctum'` so that permissions apply
 * to both web-session and mobile-token authenticated requests. Adds
 * SoftDeletes (restored via `withTrashed` binding) and Spatie Activitylog
 * so that every permission create/update/delete is audited.
 *
 * @property int              $id          Auto-incrementing primary key.
 * @property string           $name        Kebab-case permission identifier (e.g. `informe.view`).
 * @property string           $guard_name  Always `'sanctum'` for this application.
 * @property \Carbon\Carbon|null $deleted_at  Timestamp of soft deletion; null when active.
 * @property \Carbon\Carbon   $created_at  Timestamp of record creation.
 * @property \Carbon\Carbon   $updated_at  Timestamp of last modification.
 */
class Permission extends SpatiePermission
{
    use LogsActivity, SoftDeletes;

    protected string $guard_name = 'sanctum';

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(User::class, 'model', config('permission.table_names.model_has_permissions'));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'guard_name'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }
}
