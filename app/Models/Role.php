<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Custom Role model extending Spatie Permission's Role.
 *
 * Overrides the default guard to `'sanctum'` so that role checks apply
 * to both web-session and mobile-token authenticated requests. Adds
 * SoftDeletes (restored via `withTrashed` binding) and Spatie Activitylog
 * so that every role create/update/delete is audited.
 *
 * @property int $id Auto-incrementing primary key.
 * @property string $name Human-readable role name (e.g. `'auditor'`, `'admin'`).
 * @property string $guard_name Always `'sanctum'` for this application.
 * @property Carbon|null $deleted_at Timestamp of soft deletion; null when active.
 * @property Carbon $created_at Timestamp of record creation.
 * @property Carbon $updated_at Timestamp of last modification.
 */
class Role extends SpatieRole
{
    use LogsActivity, SoftDeletes;

    protected string $guard_name = 'sanctum';

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(User::class, 'model', config('permission.table_names.model_has_roles'));
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
