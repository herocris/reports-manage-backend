<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the three base roles (super-admin, admin, user) and the full
 * set of 75 resource permissions scoped to the `sanctum` guard.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.restore',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
            'permissions.restore',
            'ambitos.view',
            'ambitos.create',
            'ambitos.update',
            'ambitos.delete',
            'ambitos.restore',
            'tipos_bien.view',
            'tipos_bien.create',
            'tipos_bien.update',
            'tipos_bien.delete',
            'tipos_bien.restore',
            'productos.view',
            'productos.create',
            'productos.update',
            'productos.delete',
            'productos.restore',
            'personas.view',
            'personas.create',
            'personas.update',
            'personas.delete',
            'personas.restore',
            'instituciones.view',
            'instituciones.create',
            'instituciones.update',
            'instituciones.delete',
            'instituciones.restore',
            'ubicaciones.view',
            'ubicaciones.create',
            'ubicaciones.update',
            'ubicaciones.delete',
            'ubicaciones.restore',
            'bienes.view',
            'bienes.create',
            'bienes.update',
            'bienes.delete',
            'bienes.restore',
            'informes.view',
            'informes.create',
            'informes.update',
            'informes.delete',
            'informes.restore',
            'observaciones.view',
            'observaciones.create',
            'observaciones.update',
            'observaciones.delete',
            'observaciones.restore',
            'bitacoras.view',
            'bitacoras.create',
            'bitacoras.update',
            'bitacoras.delete',
            'bitacoras.restore',
            'adjuntos.view',
            'adjuntos.create',
            'adjuntos.update',
            'adjuntos.delete',
            'adjuntos.restore',
            'activity-log.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'sanctum');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::findOrCreate('super-admin', 'sanctum');
        $adminRole = Role::findOrCreate('admin', 'sanctum');
        $userRole = Role::findOrCreate('user', 'sanctum');

        $superAdminRole->syncPermissions(Permission::query()->pluck('name')->all());
        $adminRole->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'roles.view',
            'permissions.view',
            'ambitos.view',
            'tipos_bien.view',
            'productos.view',
            'personas.view',
            'instituciones.view',
            'ubicaciones.view',
            'bienes.view',
            'informes.view',
            'observaciones.view',
            'bitacoras.view',
            'adjuntos.view',
            'activity-log.view',
        ]);
        $userRole->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
