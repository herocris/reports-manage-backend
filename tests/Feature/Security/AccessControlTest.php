<?php

namespace Tests\Feature\Security;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_user_edit_permission_cannot_assign_roles_even_to_self(): void
    {
        $actor = $this->createAuthorizedUser(['users.update']);
        $role = Role::findOrCreate('reader', 'sanctum');

        $this->actingAs($actor)->patchJson("/api/v1/users/{$actor->id}", [
            'roles' => [$role->name],
        ])->assertForbidden();

        $this->assertFalse($actor->fresh()->hasRole($role));
    }

    public function test_role_assignment_cannot_delegate_permissions_the_actor_does_not_have(): void
    {
        $actor = $this->createAuthorizedUser(['users.update', 'users.assign-roles']);
        $permission = Permission::findOrCreate('reports.secret', 'sanctum');
        $role = Role::findOrCreate('privileged', 'sanctum');
        $role->givePermissionTo($permission);
        $target = User::factory()->create();

        $this->actingAs($actor)->patchJson("/api/v1/users/{$target->id}", [
            'roles' => [$role->name],
        ])->assertForbidden();
        $this->assertFalse($target->fresh()->hasRole($role));
    }

    public function test_role_assignment_can_delegate_owned_permissions(): void
    {
        $actor = $this->createAuthorizedUser(['users.update', 'users.assign-roles', 'reports.read']);
        $role = Role::findOrCreate('reader', 'sanctum');
        $role->givePermissionTo('reports.read');
        $target = User::factory()->create();

        $this->actingAs($actor)->patchJson("/api/v1/users/{$target->id}", [
            'roles' => [$role->name],
        ])->assertOk();

        $this->assertTrue($target->fresh()->hasRole($role));
    }

    public function test_user_editor_cannot_take_over_a_more_privileged_account(): void
    {
        $actor = $this->createAuthorizedUser(['users.update', 'users.delete', 'users.restore']);
        $target = $this->createAuthorizedUser(['roles.update']);

        $this->actingAs($actor)->patchJson("/api/v1/users/{$target->id}", [
            'email' => 'takeover@example.com', 'password' => 'NewPassword123!',
        ])->assertForbidden();
        $this->actingAs($actor)->deleteJson("/api/v1/users/{$target->id}")->assertForbidden();
        $target->delete();
        $this->actingAs($actor)->postJson("/api/v1/users/{$target->id}/restore")->assertForbidden();

        $this->assertNotSame('takeover@example.com', User::withTrashed()->findOrFail($target->id)->email);
    }

    public function test_role_editor_cannot_add_unowned_permissions_or_modify_a_privileged_role(): void
    {
        $actor = $this->createAuthorizedUser(['roles.create', 'roles.update', 'roles.delete']);
        $permission = Permission::findOrCreate('reports.secret', 'sanctum');
        $role = Role::findOrCreate('privileged', 'sanctum');
        $role->givePermissionTo($permission);

        $this->actingAs($actor)->postJson('/api/v1/roles', [
            'name' => 'elevated', 'permissions' => [$permission->name],
        ])->assertForbidden();
        $this->actingAs($actor)->patchJson("/api/v1/roles/{$role->id}", [
            'permissions' => [],
        ])->assertForbidden();
        $this->actingAs($actor)->deleteJson("/api/v1/roles/{$role->id}")->assertForbidden();
        $this->assertTrue($role->fresh()->hasPermissionTo($permission));
        $this->assertDatabaseMissing('roles', ['name' => 'elevated']);
    }

    public function test_builtin_permissions_cannot_be_renamed_or_deleted(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.update', 'permissions.delete', 'users.update']);
        $permission = Permission::findByName('users.update', 'sanctum');

        $this->actingAs($actor)->patchJson("/api/v1/permissions/{$permission->id}", [
            'name' => 'renamed.permission',
        ])->assertForbidden();
        $this->actingAs($actor)->deleteJson("/api/v1/permissions/{$permission->id}")->assertForbidden();
        $this->assertNotSoftDeleted($permission);
    }

    public function test_custom_permission_cannot_be_renamed_into_a_builtin_permission(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.update']);
        $permission = Permission::findOrCreate('reports.custom', 'sanctum');

        $this->actingAs($actor)->patchJson("/api/v1/permissions/{$permission->id}", [
            'name' => 'users.assign-roles',
        ])->assertForbidden();
        $this->assertSame('reports.custom', $permission->fresh()->name);
    }

    public function test_permission_editor_cannot_grant_an_unowned_permission_through_roles(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.update']);
        $permission = Permission::findOrCreate('reports.secret', 'sanctum');
        $role = Role::findOrCreate('reader', 'sanctum');

        $this->actingAs($actor)->patchJson("/api/v1/permissions/{$permission->id}", [
            'roles' => [$role->name],
        ])->assertForbidden();
        $this->assertFalse($role->fresh()->hasPermissionTo($permission));
    }

    public function test_soft_deleted_roles_are_not_assignable(): void
    {
        $actor = $this->createAuthorizedUser(['users.update', 'users.assign-roles']);
        $role = Role::findOrCreate('deleted-role', 'sanctum');
        $role->delete();
        $target = User::factory()->create();

        $this->actingAs($actor)->patchJson("/api/v1/users/{$target->id}", [
            'roles' => [$role->name],
        ])->assertUnprocessable()->assertJsonValidationErrors('roles.0');
    }

    public function test_password_hashes_are_excluded_from_new_audit_entries(): void
    {
        $user = User::factory()->create();
        $activity = ActivityLog::query()->where('subject_type', User::class)->where('subject_id', $user->id)->latest('id')->firstOrFail();

        $this->assertStringNotContainsString($user->password, $activity->toJson());
        $this->assertArrayNotHasKey('password', $activity->attribute_changes['attributes'] ?? []);
    }

    public function test_historical_passwords_are_redacted_from_both_audit_payload_columns(): void
    {
        $actor = $this->createAuthorizedUser(['activity-log.view']);
        $entry = ActivityLog::query()->create([
            'log_name' => 'default',
            'description' => 'legacy user update',
            'event' => 'updated',
            'properties' => ['old' => ['password' => 'legacy-secret'], 'safe' => 'kept'],
            'attribute_changes' => ['attributes' => ['password' => 'legacy-hash', 'name' => 'Visible']],
        ]);

        $response = $this->actingAs($actor)->getJson('/api/v1/activity-logs');
        $response->assertOk()->assertDontSee('legacy-secret')->assertDontSee('legacy-hash');
        $row = collect($response->json('data'))->firstWhere('id', $entry->id);
        $this->assertSame('kept', $row['properties']['safe']);
        $this->assertSame('Visible', $row['attribute_changes']['attributes']['name']);
    }

    public function test_permission_catalog_includes_separate_role_assignment_permission(): void
    {
        $this->assertContains('users.assign-roles', PermissionCatalog::names());
        $this->assertSame(count(PermissionCatalog::names()), count(array_unique(PermissionCatalog::names())));
    }

    public function test_custom_permission_rename_cannot_change_a_more_privileged_role_indirectly(): void
    {
        $actor = $this->createAuthorizedUser(['permissions.update', 'reports.custom']);
        $secret = Permission::findOrCreate('reports.secret', 'sanctum');
        $role = Role::findOrCreate('privileged', 'sanctum');
        $role->givePermissionTo(['reports.custom', $secret]);
        $permission = Permission::findByName('reports.custom', 'sanctum');

        $this->actingAs($actor)->patchJson("/api/v1/permissions/{$permission->id}", [
            'name' => 'reports.renamed',
        ])->assertForbidden();

        $this->assertSame('reports.custom', $permission->fresh()->name);
    }

    public function test_restoring_a_role_checks_its_soft_deleted_permissions_too(): void
    {
        $actor = $this->createAuthorizedUser(['roles.restore']);
        $permission = Permission::findOrCreate('reports.secret', 'sanctum');
        $role = Role::findOrCreate('dormant-privileged', 'sanctum');
        $role->givePermissionTo($permission);
        $role->delete();
        $permission->delete();

        $this->actingAs($actor)->postJson("/api/v1/roles/{$role->id}/restore")->assertForbidden();
        $this->assertSoftDeleted($role);
    }

    public function test_dormant_user_privileges_prevent_account_takeover_and_restoration(): void
    {
        $actor = $this->createAuthorizedUser(['users.update', 'users.restore']);
        $target = User::factory()->create();
        $permission = Permission::findOrCreate('reports.secret', 'sanctum');
        $role = Role::findOrCreate('dormant-privileged', 'sanctum');
        $role->givePermissionTo($permission);
        $target->assignRole($role);
        $role->delete();
        $permission->delete();
        $originalPassword = $target->password;

        $this->actingAs($actor)->patchJson("/api/v1/users/{$target->id}", [
            'email' => 'takeover@example.com', 'password' => 'NewPassword123!',
        ])->assertForbidden();
        $this->assertSame($originalPassword, $target->fresh()->password);
        $this->assertNotSame('takeover@example.com', $target->fresh()->email);

        $target->delete();
        $this->actingAs($actor)->postJson("/api/v1/users/{$target->id}/restore")->assertForbidden();
        $this->assertSoftDeleted($target);
    }
}
