<?php

namespace Tests\Feature\Database;

use App\Models\Informe;
use App\Models\Permission;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductionSeedingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_production_seed_creates_catalog_without_demo_records_or_known_accounts(): void
    {
        config(['auth.initial_admin.email' => null, 'auth.initial_admin.password' => null]);
        $this->app->instance('env', 'production');

        try {
            $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();
        } finally {
            $this->app->instance('env', 'testing');
        }

        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Informe::query()->count());
        $this->assertSame(count(PermissionCatalog::names()), Permission::query()->count());
    }

    public function test_admin_creation_requires_explicit_credentials_and_preserves_existing_password(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        config([
            'auth.initial_admin.email' => 'owner@example.org',
            'auth.initial_admin.password' => 'AnExplicitSecret123!',
        ]);
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'owner@example.org')->firstOrFail();
        $this->assertTrue(Hash::check('AnExplicitSecret123!', $admin->password));
        $this->assertTrue($admin->hasRole('super-admin'));

        config(['auth.initial_admin.password' => 'AnotherSecret123!']);
        $this->seed(AdminUserSeeder::class);
        $this->assertSame(1, User::query()->count());
        $this->assertTrue(Hash::check('AnExplicitSecret123!', $admin->fresh()->password));
    }

    public function test_admin_seed_rejects_a_known_short_password(): void
    {
        config(['auth.initial_admin.email' => 'owner@example.org', 'auth.initial_admin.password' => 'password']);
        $this->expectException(ValidationException::class);
        $this->seed(AdminUserSeeder::class);
    }

    public function test_permission_seed_is_idempotent_and_restores_integrated_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        Permission::findByName('users.update', 'sanctum')->delete();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(count(PermissionCatalog::names()), Permission::withTrashed()->count());
        $this->assertFalse(Permission::findByName('users.update', 'sanctum')->trashed());
    }

    public function test_admin_seed_never_promotes_an_existing_unprivileged_account(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create(['email' => 'existing@example.org']);
        config(['auth.initial_admin.email' => $user->email, 'auth.initial_admin.password' => 'ExplicitSecret123!']);

        try {
            $this->seed(AdminUserSeeder::class);
            $this->fail('Existing accounts must not be promoted by the initial admin seeder.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('email', $exception->errors());
        }

        $this->assertFalse($user->fresh()->hasRole('super-admin'));
    }

    public function test_admin_seed_rejects_a_soft_deleted_existing_email(): void
    {
        $user = User::factory()->create(['email' => 'deleted@example.org']);
        $user->delete();
        config(['auth.initial_admin.email' => $user->email, 'auth.initial_admin.password' => 'ExplicitSecret123!']);

        $this->expectException(ValidationException::class);
        $this->seed(AdminUserSeeder::class);
    }
}
