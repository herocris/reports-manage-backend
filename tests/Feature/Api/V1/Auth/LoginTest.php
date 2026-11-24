<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for web and mobile login flows,
 * including invalid credential and missing-field scenarios.
 */
class LoginTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_user_can_login_on_mobile_endpoint_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user' => ['id', 'name', 'email']],
            ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'event' => 'login',
            'description' => 'auth.login',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }

    public function test_user_can_login_on_web_endpoint_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/web/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user' => ['id', 'name', 'email', 'roles', 'permissions']],
            ])
            ->assertJsonMissingPath('data.token');
    }

    public function test_frontend_can_access_informes_with_the_web_login_session_cookie(): void
    {
        $user = $this->createAuthorizedUser(['informes.view']);
        $this->withHeader('Origin', 'http://localhost:5174');

        $response = $this->postJson('/api/v1/auth/web/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $cookieName = config('session.cookie');

        $response->assertOk()
            ->assertCookie($cookieName)
            ->assertJsonMissingPath('data.token');

        $sessionCookie = $response->getCookie($cookieName, false);
        $uri = '/api/v1/informes?include=producto%2Cambitos&page=1&per_page=10&sort=id&direction=asc';

        $this->app['auth']->forgetGuards();
        $this->app['session.store']->flush();

        $this->getJson($uri)->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->app['session.store']->flush();

        $this->withUnencryptedCookie($cookieName, $sessionCookie->getValue())
            ->withCredentials()
            ->getJson($uri)
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5174')
            ->assertHeader('Access-Control-Allow-Credentials', 'true')
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_web_login_includes_permissions_granted_via_role(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('admin', 'sanctum');
        $permission = Permission::findOrCreate('reports.view', 'sanctum');
        $role->syncPermissions([$permission]);
        $user->assignRole($role);

        $response = $this->postJson('/api/v1/auth/web/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.roles.0', 'admin');

        $this->assertContains('reports.view', $response->json('data.user.permissions', []));
    }

    public function test_web_login_for_super_admin_includes_its_granted_permissions(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('users.view', 'sanctum');
        Permission::findOrCreate('roles.view', 'sanctum');

        $superAdminRole = Role::findOrCreate('super-admin', 'sanctum');
        $superAdminRole->syncPermissions(['users.view', 'roles.view']);
        $user->assignRole($superAdminRole);

        $response = $this->postJson('/api/v1/auth/web/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.roles.0', 'super-admin');

        $permissions = $response->json('data.user.permissions', []);
        $this->assertContains('users.view', $permissions);
        $this->assertContains('roles.view', $permissions);
    }

    public function test_role_names_do_not_advertise_permissions_that_were_not_granted(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('users.delete', 'sanctum');
        $user->assignRole(Role::findOrCreate('super-admin', 'sanctum'));

        $this->postJson('/api/v1/auth/web/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonPath('data.user.permissions', []);
    }

    public function test_legacy_login_endpoint_remains_available_for_mobile_clients(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure([
            'success',
            'message',
            'data' => ['token', 'user' => ['id', 'name', 'email']],
        ]);
    }

    public function test_login_returns_401_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->postJson('/api/v1/auth/mobile/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'event' => 'failed',
            'description' => 'auth.failed',
        ]);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/mobile/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_validates_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/mobile/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
