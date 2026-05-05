<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Permission\IndexPermissionRequest;
use App\Http\Requests\Api\V1\Permission\StorePermissionRequest;
use App\Http\Requests\Api\V1\Permission\UpdatePermissionRequest;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Models\Permission;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\PermissionIndexDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for Permission records (Spatie Permission, guard `sanctum`).
 *
 * Permissions are the atomic authorization units assigned to roles or directly
 * to users. The `store` action always sets `guard_name` to `'sanctum'`. Each
 * response includes a `users_count` and the `roles` relation so that the
 * full assignment context is available to the client.
 *
 * Roles can be synced in both `store` and `update`; omitting `roles` in
 * `update` preserves the existing role assignment.
 */
class PermissionController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of permissions.
     *
     * The base query includes `withCount('users')` so that the number of users
     * (directly or via roles) holding each permission is available in the response.
     *
     * @param  IndexPermissionRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<\Illuminate\Contracts\Pagination\LengthAwarePaginator<PermissionResource>>
     */
    public function index(IndexPermissionRequest $request): AnonymousResourceCollection
    {
        $permissions = $this->applyIndexQuery(
            Permission::query()->withCount('users'),
            $request->validated(),
            PermissionIndexDefinition::definition(),
        );

        return PermissionResource::collection($permissions)
            ->additional([
                'success' => true,
                'message' => 'Permissions retrieved successfully.',
            ]);
    }

    /**
     * Create a new permission and optionally assign it to roles.
     *
     * `guard_name` is always forced to `'sanctum'`. If `roles` is present,
     * `syncRoles()` replaces all role assignments on the new permission.
     *
     * @param  StorePermissionRequest  $request  Validated creation payload (`name`, optional `roles[]`).
     * @return JsonResponse  201 with the created {@see PermissionResource} envelope including `roles` and `users_count`.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create([
            'name' => $request->validated('name'),
            'guard_name' => 'sanctum',
        ]);

        $permission->syncRoles($request->validated('roles', []));

        return (new PermissionResource($permission->load('roles')->loadCount('users')))
            ->additional([
                'success' => true,
                'message' => 'Permission created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single permission by its primary key.
     *
     * Eager-loads `roles` and the `users_count` aggregate.
     *
     * @param  Permission  $permission  The resolved Permission model instance.
     * @return PermissionResource  200 with the resource envelope.
     */
    public function show(Permission $permission): PermissionResource
    {
        return (new PermissionResource($permission->load('roles')->loadCount('users')))
            ->additional([
                'success' => true,
                'message' => 'Permission retrieved successfully.',
            ]);
    }

    /**
     * Apply a partial or full update to the given permission.
     *
     * Only `name` is updatable on the permission record itself. When `roles`
     * is present, the permission's role assignments are fully replaced;
     * omitting `roles` preserves existing assignments.
     *
     * @param  UpdatePermissionRequest  $request    Validated update payload.
     * @param  Permission               $permission  The resolved Permission model instance.
     * @return PermissionResource  200 with the updated resource envelope.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): PermissionResource
    {
        $permission->update([
            'name' => $request->validated('name', $permission->name),
        ]);

        if ($request->has('roles')) {
            $permission->syncRoles($request->validated('roles', []));
        }

        return (new PermissionResource($permission->load('roles')->loadCount('users')))
            ->additional([
                'success' => true,
                'message' => 'Permission updated successfully.',
            ]);
    }

    /**
     * Soft-delete the given permission.
     *
     * Sets `deleted_at`; roles that include this permission retain the
     * assignment but the permission will not resolve in authorization checks
     * until restored.
     *
     * @param  Permission  $permission  The resolved Permission model instance.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return ApiResponse::success(null, 'Permission deleted successfully.');
    }

    /**
     * Restore a soft-deleted permission.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Permission  $permission  The soft-deleted Permission model instance to restore.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function restore(Permission $permission): JsonResponse
    {
        $permission->restore();

        return ApiResponse::success(null, 'Permission restored successfully.');
    }
}
