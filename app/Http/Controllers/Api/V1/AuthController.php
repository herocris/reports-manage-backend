<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles authentication for both web (session/cookie) and mobile (bearer token) clients.
 *
 * Two distinct authentication flows are supported:
 *
 * **Mobile / API flow** (stateless, token-based):
 *   1. `POST /api/v1/auth/mobile/login` → credentials exchanged for a Sanctum personal access token.
 *   2. All subsequent requests include `Authorization: Bearer {token}`.
 *   3. `POST /api/v1/auth/mobile/logout` revokes only the token used in that request (per-device).
 *
 * **Web / SPA flow** (stateful, cookie-based):
 *   1. `GET /sanctum/csrf-cookie` → obtains the XSRF-TOKEN cookie.
 *   2. `POST /api/v1/auth/web/login` → credentials verified, session started.
 *   3. All subsequent requests use the session cookie; no `Authorization` header needed.
 *   4. `POST /api/v1/auth/web/logout` invalidates the session and regenerates its CSRF token.
 *
 * All authentication events are recorded in the activity log via {@see logLogout()}.
 */
class AuthController extends Controller
{
    /**
     * Register a SPA user and authenticate using a web session cookie.
     *
     * Creates the user account, dispatches the `Registered` event, logs the
     * user into the `web` guard, regenerates the session ID, and returns the
     * authenticated user resource without issuing an API token.
     *
     * @param  RegisterRequest  $request  Validated registration payload.
     * @return JsonResponse 201 `{success, message, data: {user: UserResource}}`.
     */
    public function registerWeb(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        event(new Registered($user));

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return ApiResponse::success([
            'user' => new UserResource($user->loadMissing(['roles', 'permissions'])),
        ], 'Registered successfully.', 201);
    }

    /**
     * Register a mobile user and issue a Sanctum personal access token.
     *
     * Creates the user account, dispatches the `Registered` event, and returns
     * a newly-issued personal access token plus user data.
     *
     * @param  RegisterRequest  $request  Validated registration payload.
     * @return JsonResponse 201 `{success, message, data: {token: string, user: UserResource}}`.
     */
    public function registerMobile(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        event(new Registered($user));

        $deviceName = $request->string('device_name', 'api-token')->value();
        $token = $user->createToken($deviceName)->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user->loadMissing(['roles', 'permissions'])),
        ], 'Registered successfully.', 201);
    }

    /**
     * Legacy login alias — delegates to {@see loginMobile()}.
     *
     * Preserved for backwards compatibility with older clients.
     * New integrations should use `POST /api/v1/auth/mobile/login` directly.
     *
     * @deprecated Use POST /api/v1/auth/mobile/login instead.
     *
     * @param  LoginRequest  $request  Validated login credentials.
     * @return JsonResponse Same response as {@see loginMobile()}.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->loginMobile($request);
    }

    /**
     * Authenticate a SPA client using Laravel session cookies.
     *
     * Validates credentials against the default guard, regenerates the
     * session ID to prevent session-fixation attacks, and returns the
     * authenticated user. The session cookie is handled transparently
     * by the browser; no token is issued or returned.
     *
     * Returns 401 if the email/password combination is invalid.
     *
     * @param  LoginRequest  $request  Validated credentials (`email`, `password`, optional `device_name`).
     * @return JsonResponse 200 `{success, message, data: {user: UserResource}}`.
     */
    public function loginWeb(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::error('Invalid credentials.', 401);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        /** @var User $user */
        $user = $request->user()->loadMissing(['roles', 'permissions']);

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Logged in successfully.');
    }

    /**
     * Authenticate a mobile or third-party API client and issue a Sanctum personal access token.
     *
     * Validates credentials against the default guard and creates a new
     * personal access token scoped to the authenticated user. The optional
     * `device_name` field in the request allows labelling the token per device
     * (defaults to `'api-token'`). Multiple tokens can coexist for the same
     * user across different devices; each logout revokes only its own token.
     *
     * Returns 401 if the email/password combination is invalid.
     *
     * @param  LoginRequest  $request  Validated credentials (`email`, `password`, optional `device_name`).
     * @return JsonResponse 200 `{success, message, data: {token: string, user: UserResource}}`.
     */
    public function loginMobile(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::error('Invalid credentials.', 401);
        }

        /** @var User $user */
        $user = Auth::user()->loadMissing(['roles', 'permissions']);
        $deviceName = $request->string('device_name', 'api-token')->value();
        $token = $user->createToken($deviceName)->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Logged in successfully.');
    }

    /**
     * Return the currently authenticated user's own profile.
     *
     * Works for both web (session) and mobile (bearer token) guards.
     * Includes roles, permissions, and — exclusively for the owner —
     * the `email_verified_at` field (see {@see UserResource::toArray()}).
     *
     * @param  Request  $request  The current incoming request with an authenticated user.
     * @return JsonResponse 200 `{success, message, data: {user: UserResource}}`.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing(['roles', 'permissions']);

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Profile retrieved successfully.');
    }

    /**
     * Update the authenticated user's own password.
     *
     * Only the `password` field may be changed via this endpoint; name, email,
     * `institucion_id`, and roles are prohibited by {@see UpdateProfileRequest}.
     * The new password is hashed automatically via the `hashed` cast on the model.
     *
     * @param  UpdateProfileRequest  $request  Validated payload containing the new `password`.
     * @return JsonResponse 200 `{success, message, data: {user: UserResource}}`.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->update($request->validated());

        return ApiResponse::success([
            'user' => new UserResource($user->fresh()->loadMissing(['roles', 'permissions'])),
        ], 'Profile updated successfully.');
    }

    /**
     * Legacy logout alias — delegates to {@see logoutMobile()}.
     *
     * Preserved for backwards compatibility with older clients.
     * New integrations should use `POST /api/v1/auth/mobile/logout` directly.
     *
     * @deprecated Use POST /api/v1/auth/mobile/logout instead.
     *
     * @param  Request  $request  The current incoming request.
     * @return JsonResponse Same response as {@see logoutMobile()}.
     */
    public function logout(Request $request): JsonResponse
    {
        return $this->logoutMobile($request);
    }

    /**
     * Invalidate the web session and rotate its CSRF token.
     *
     * Records a logout activity log entry, then calls
     * `Auth::guard('web')->logout()`, `session()->invalidate()`, and
     * `session()->regenerateToken()` to fully tear down the session.
     * Safe to call even if no session exists.
     *
     * @param  Request  $request  The current incoming request (session-based).
     * @return JsonResponse 200 `{success, message, data: {user: UserResource}}`.
     */
    public function logoutWeb(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->logLogout($request, 'web');

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Logged out successfully.');
    }

    /**
     * Revoke only the Sanctum token that authenticated this request (per-device logout).
     *
     * Calls `currentAccessToken()->delete()` so only the token presented
     * in the `Authorization: Bearer` header is invalidated. All other tokens
     * issued to the same user (from other devices or sessions) remain valid.
     * Records a logout activity log entry before revoking the token.
     *
     * @param  Request  $request  The current incoming request with a valid Sanctum token.
     * @return JsonResponse 200 `{success, message, data: null}`.
     */
    public function logoutMobile(Request $request): JsonResponse
    {
        $this->logLogout($request, 'sanctum');

        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    /**
     * Record a logout event in the Spatie activity log under the `auth` log name.
     *
     * Captures the guard name, user agent, and IP address so that audit trails
     * remain complete even after the session or token has been destroyed.
     *
     * @param  Request  $request  The current incoming request (used for IP / user-agent metadata).
     * @param  string  $guard  The name of the guard that was used: `'web'` or `'sanctum'`.
     */
    private function logLogout(Request $request, string $guard): void
    {
        activity('auth')
            ->event('logout')
            ->causedBy($request->user())
            ->performedOn($request->user())
            ->withProperties([
                'guard' => $guard,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ])
            ->log('auth.logout');
    }
}
