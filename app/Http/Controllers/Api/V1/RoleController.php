<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Role\IndexRoleRequest;
use App\Http\Requests\Api\V1\Role\StoreRoleRequest;
use App\Http\Requests\Api\V1\Role\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleResource;
use App\Models\Role;
use App\Support\Authorization\AccessControl;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\RoleIndexDefinition;
use App\Support\Query\IndexQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * Handles CRUD operations for Role records (Spatie Permission, guard `sanctum`).
 *
 * Roles group permissions and are assigned to users. The `store` action
 * always sets `guard_name` to `'sanctum'`; the client should not supply this.
 * Each response includes a `users_count` aggregate for auditing.
 *
 * Permissions can be synced in both `store` and `update`; omitting `permissions`
 * in `update` preserves the existing assignment.
 */
class RoleController extends Controller
{
    public function __construct(private AccessControl $accessControl) {}

    /**
     * Return a paginated, filterable list of roles.
     *
     * The base query includes `withCount('users')` so that the number of users
     * assigned to each role is available in the response.
     *
     * @param  IndexRoleRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<RoleResource>>
     */
    public function index(IndexRoleRequest $request, IndexQueryApplier $indexQueryApplier): AnonymousResourceCollection
    {
        $roles = $indexQueryApplier->apply(
            Role::query()->withCount('users'),
            $request->validated(),
            RoleIndexDefinition::definition(),
        );

        return RoleResource::collection($roles)
            ->additional([
                'success' => true,
                'message' => 'Roles retrieved successfully.',
            ]);
    }

    /**
     * Create a new role and optionally sync its permissions.
     *
     * `guard_name` is always forced to `'sanctum'` regardless of any client
     * input. If `permissions` is present, `syncPermissions()` replaces all
     * existing permission assignments on the new role.
     *
     * @param  StoreRoleRequest  $request  Validated creation payload (`name`, optional `permissions[]`).
     * @return JsonResponse 201 with the created {@see RoleResource} envelope including `permissions` and `users_count`.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->accessControl->authorizePermissions($request->user(), $request->validated('permissions', []));
        $role = DB::transaction(function () use ($request): Role {
            $role = Role::create([
                'name' => $request->validated('name'),
                'guard_name' => 'sanctum',
            ]);
            $role->syncPermissions($request->validated('permissions', []));

            return $role;
        });

        return (new RoleResource($role->load('permissions')->loadCount('users')))
            ->additional([
                'success' => true,
                'message' => 'Role created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single role by its primary key.
     *
     * Eager-loads `permissions` and the `users_count` aggregate.
     *
     * @param  Role  $role  The resolved Role model instance.
     * @return RoleResource 200 with the resource envelope.
     */
    public function show(Role $role): RoleResource
    {
        return (new RoleResource($role->load('permissions')->loadCount('users')))
            ->additional([
                'success' => true,
                'message' => 'Role retrieved successfully.',
            ]);
    }

    /**
     * Apply a partial or full update to the given role.
     *
     * Only the `name` field is updatable on the role itself. When `permissions`
     * is present in the request, the role's permission assignments are fully
     * replaced; omitting `permissions` leaves existing assignments unchanged.
     *
     * @param  UpdateRoleRequest  $request  Validated update payload.
     * @param  Role  $role  The resolved Role model instance.
     * @return RoleResource 200 with the updated resource envelope.
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $this->accessControl->authorizeRole($request->user(), $role);
        $this->accessControl->authorizePermissions($request->user(), $request->validated('permissions', []));
        DB::transaction(function () use ($request, $role): void {
            $role->update(['name' => $request->validated('name', $role->name)]);
            if ($request->has('permissions')) {
                $role->syncPermissions($request->validated('permissions', []));
            }
        });

        return (new RoleResource($role->load('permissions')->loadCount('users')))
            ->additional([
                'success' => true,
                'message' => 'Role updated successfully.',
            ]);
    }

    /**
     * Soft-delete the given role.
     *
     * Sets `deleted_at`; users currently assigned this role retain the
     * assignment but the role will not resolve in permission checks until restored.
     *
     * @param  Role  $role  The resolved Role model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->accessControl->authorizeRole($request->user(), $role);
        $role->delete();

        return ApiResponse::success(null, 'Role deleted successfully.');
    }

    /**
     * Restore a soft-deleted role.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Role  $role  The soft-deleted Role model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Request $request, Role $role): JsonResponse
    {
        $this->accessControl->authorizeRole($request->user(), $role);
        $role->restore();

        return ApiResponse::success(null, 'Role restored successfully.');
    }
}
