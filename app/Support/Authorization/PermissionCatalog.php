<?php

namespace App\Support\Authorization;

class PermissionCatalog
{
    /** @return list<string> */
    public static function names(): array
    {
        $permissions = ['users.assign-roles', 'activity-log.view'];

        foreach (['users', 'roles', 'permissions', 'ambitos', 'tipos_bien', 'productos', 'personas', 'instituciones', 'ubicaciones', 'bienes', 'informes', 'observaciones', 'bitacoras', 'adjuntos'] as $resource) {
            foreach (['view', 'create', 'update', 'delete', 'restore'] as $action) {
                $permissions[] = $resource.'.'.$action;
            }
        }

        return $permissions;
    }

    public static function contains(string $name): bool
    {
        return in_array($name, self::names(), true);
    }
}
