<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionCatalog::names() as $name) {
            $permission = Permission::withTrashed()->firstOrCreate(['name' => $name, 'guard_name' => 'sanctum']);
            if ($permission->trashed()) {
                $permission->restore();
            }
        }

        foreach (['super-admin', 'admin', 'user'] as $name) {
            $role = Role::withTrashed()->firstOrCreate(['name' => $name, 'guard_name' => 'sanctum']);
            if ($role->trashed()) {
                $role->restore();
            }
        }

        Role::findByName('super-admin', 'sanctum')->syncPermissions(PermissionCatalog::names());
        Role::findByName('admin', 'sanctum')->syncPermissions([
            'users.view', 'users.create', 'users.update', 'roles.view', 'permissions.view',
            'ambitos.view', 'tipos_bien.view', 'productos.view', 'personas.view',
            'instituciones.view', 'ubicaciones.view', 'bienes.view', 'informes.view',
            'observaciones.view', 'bitacoras.view', 'adjuntos.view', 'activity-log.view',
        ]);
        Role::findByName('user', 'sanctum')->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
