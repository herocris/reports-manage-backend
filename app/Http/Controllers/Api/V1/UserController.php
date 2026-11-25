<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\IndexUserRequest;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Support\Authorization\AccessControl;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\UserIndexDefinition;
use App\Support\Query\IndexQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * Handles CRUD operations for User accounts, including role synchronization.
 *
 * User creation and updates both accept a `roles` array that is synced
 * against the Spatie Permission role table (guard `sanctum`). When
 * `roles` is omitted from an update, existing role assignments are preserved.
 *
 * The {@see UserResource} applies field-level security: `email_verified_at`
 * is only exposed when the requesting user is viewing their own profile or
 * has the `user.view` permission.
 */
class UserController extends Controller
{
    public function __construct(private AccessControl $accessControl) {}

    /**
     * Return a paginated, filterable list of users.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see UserIndexDefinition}. Supports search across `name` and `email`,
     * per-field filters, eager-loading of `roles` and `permissions`,
     * field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexUserRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<UserResource>>
     */
    public function index(IndexUserRequest $request, IndexQueryApplier $indexQueryApplier): AnonymousResourceCollection
    {
        $users = $indexQueryApplier->apply(
            User::query(),
            $request->validated(),
            UserIndexDefinition::definition(),
        );

        return UserResource::collection($users)
            ->additional([
                'success' => true,
                'message' => 'Users retrieved successfully.',
            ]);
    }

    /**
     * Create a new user account and optionally assign roles.
     *
     * The password is hashed automatically via the `hashed` cast on the
     * {@see User} model. If `roles` is present in the validated payload,
     * the user's roles are synced via `syncRoles()`. The response eagerly
     * loads `roles` and `permissions` on the returned resource.
     *
     * @param  StoreUserRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see UserResource} envelope.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if (array_key_exists('roles', $validated)) {
            $this->accessControl->authorizeRoleAssignment($request->user(), $validated['roles']);
        }

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create(collect($validated)->except('roles')->all());
            if (array_key_exists('roles', $validated)) {
                $user->syncRoles($validated['roles']);
            }

            return $user;
        });

        return (new UserResource($user->load('roles', 'permissions')))
            ->additional([
                'success' => true,
                'message' => 'User created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single user by their primary key, including roles and permissions.
     *
     * Eager-loads `roles` and `permissions` so the response contains the
     * full authorization context for the user. The `email_verified_at` field
     * is redacted unless the requester is the owner or has `user.view` permission.
     *
     * @param  User  $user  The resolved User model instance.
     * @return UserResource 200 with the resource envelope.
     */
    public function show(User $user): UserResource
    {
        return (new UserResource($user->load('roles', 'permissions')))
            ->additional([
                'success' => true,
                'message' => 'User retrieved successfully.',
            ]);
    }

    /**
     * Apply a partial or full update to the user account.
     *
     * When `roles` is present in the validated payload, the user's role
     * assignments are fully replaced via `syncRoles()`. When `roles` is absent,
     * existing assignments are preserved. The response includes freshly
     * loaded `roles` and `permissions` to reflect any changes.
     *
     * @param  UpdateUserRequest  $request  Validated update payload.
     * @param  User  $user  The resolved User model instance.
     * @return UserResource 200 with the updated resource envelope.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $validated = $request->validated();
        $this->accessControl->authorizeUser($request->user(), $user);

        if (array_key_exists('roles', $validated)) {
            $this->accessControl->authorizeRoleAssignment($request->user(), $validated['roles']);
        }

        DB::transaction(function () use ($user, $validated): void {
            $user->update(collect($validated)->except('roles')->all());
            if (array_key_exists('roles', $validated)) {
                $user->syncRoles($validated['roles']);
            }
        });

        return (new UserResource($user->load('roles', 'permissions')))
            ->additional([
                'success' => true,
                'message' => 'User updated successfully.',
            ]);
    }

    /**
     * Soft-delete the user account.
     *
     * Sets `deleted_at` on the user record; existing tokens, roles, and
     * permissions are not removed. The user will be unable to authenticate
     * after deletion even if they hold an active Sanctum token (the
     * `EnsureEmailIsVerified` and auth middleware reject deleted users).
     *
     * @param  User  $user  The resolved User model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->accessControl->authorizeUser($request->user(), $user);
        $user->delete();

        return ApiResponse::success(null, 'User deleted successfully.');
    }

    /**
     * Restore a soft-deleted user account.
     *
     * Clears `deleted_at` so the account is active again. Tokens, roles,
     * and permissions that were present at the time of deletion are still
     * intact and will be effective immediately after restore.
     *
     * @param  User  $user  The soft-deleted User model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Request $request, User $user): JsonResponse
    {
        $this->accessControl->authorizeUser($request->user(), $user);
        $user->restore();

        return ApiResponse::success(null, 'User restored successfully.');
    }
}
