<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Permission\IndexPermissionRequest;
use App\Http\Requests\Api\V1\Permission\StorePermissionRequest;
use App\Http\Requests\Api\V1\Permission\UpdatePermissionRequest;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Models\Permission;
use App\Support\Authorization\AccessControl;
use App\Support\Authorization\PermissionCatalog;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\PermissionIndexDefinition;
use App\Support\Query\IndexQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * Handles CRUD operations for Permission records (Spatie Permission, guard `sanctum`).
 *
 * Permissions are the atomic authorization units assigned to roles or directly
 * to users. The `store` action always sets `guard_name` to `'sanctum'`. Each
 * responses include the `roles` relation so that the assignment context is
 * available to the client.
 *
 * Roles can be synced in both `store` and `update`; omitting `roles` in
 * `update` preserves the existing role assignment.
 */
class PermissionController extends Controller
{
    public function __construct(private AccessControl $accessControl) {}

    /**
     * Return a paginated, filterable list of permissions.
     *
     * @param  IndexPermissionRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<PermissionResource>>
     */
    public function index(IndexPermissionRequest $request, IndexQueryApplier $indexQueryApplier): AnonymousResourceCollection
    {
        $permissions = $indexQueryApplier->apply(
            Permission::query(),
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
     * @return JsonResponse 201 with the created {@see PermissionResource} envelope including `roles`.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        abort_if(PermissionCatalog::contains($request->validated('name')), 403);
        $roles = $request->validated('roles', []);
        if ($roles !== []) {
            $this->accessControl->authorizePermissions($request->user(), PermissionCatalog::names());
            $this->accessControl->authorizeRoles($request->user(), $roles);
        }

        $permission = DB::transaction(function () use ($request, $roles): Permission {
            $permission = Permission::create([
                'name' => $request->validated('name'),
                'guard_name' => 'sanctum',
            ]);
            $permission->syncRoles($roles);

            return $permission;
        });

        return (new PermissionResource($permission->load('roles')))
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
     * Eager-loads `roles`.
     *
     * @param  Permission  $permission  The resolved Permission model instance.
     * @return PermissionResource 200 with the resource envelope.
     */
    public function show(Permission $permission): PermissionResource
    {
        return (new PermissionResource($permission->load('roles')))
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
     * @param  UpdatePermissionRequest  $request  Validated update payload.
     * @param  Permission  $permission  The resolved Permission model instance.
     * @return PermissionResource 200 with the updated resource envelope.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): PermissionResource
    {
        $name = $request->validated('name', $permission->name);
        abort_if($name !== $permission->name && (PermissionCatalog::contains($permission->name) || PermissionCatalog::contains($name)), 403);
        $this->accessControl->authorizePermissionTargets($request->user(), $permission);
        if ($request->has('roles')) {
            $this->accessControl->authorizePermissions($request->user(), [$permission->name]);
            $this->accessControl->authorizeRoles($request->user(), array_merge(
                $permission->roles()->pluck('name')->all(),
                $request->validated('roles', []),
            ));
        }

        DB::transaction(function () use ($request, $permission, $name): void {
            $permission->update(['name' => $name]);
            if ($request->has('roles')) {
                $permission->syncRoles($request->validated('roles', []));
            }
        });

        return (new PermissionResource($permission->load('roles')))
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
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Request $request, Permission $permission): JsonResponse
    {
        abort_if(PermissionCatalog::contains($permission->name), 403);
        $this->accessControl->authorizePermissionTargets($request->user(), $permission);
        $this->accessControl->authorizeRoles($request->user(), $permission->roles()->pluck('name')->all());
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
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Request $request, Permission $permission): JsonResponse
    {
        $this->accessControl->authorizePermissionTargets($request->user(), $permission);
        if ($permission->roles()->exists() || $permission->users()->exists()) {
            $this->accessControl->authorizePermissions($request->user(), PermissionCatalog::names());
        }
        $this->accessControl->authorizeRoles($request->user(), $permission->roles()->pluck('name')->all());
        $permission->restore();

        return ApiResponse::success(null, 'Permission restored successfully.');
    }
}
