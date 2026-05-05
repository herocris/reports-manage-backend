<?php

namespace Tests\Feature\Api\V1\Ambito;

use App\Models\Ambito;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted ambito records.
 */
class RestoreAmbitoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_ambito(): void
    {
        $actor = $this->createAuthorizedUser(['ambitos.restore']);
        $ambito = Ambito::factory()->create();
        $ambito->delete();

        $this->actingAs($actor)->postJson("/api/v1/ambitos/{$ambito->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Ambito restored successfully.']);

        $this->assertNotSoftDeleted($ambito);
    }

    public function test_user_without_permission_cannot_restore_ambito(): void
    {
        $actor = User::factory()->create();
        $ambito = Ambito::factory()->create();
        $ambito->delete();

        $this->actingAs($actor)->postJson("/api/v1/ambitos/{$ambito->id}/restore")
            ->assertForbidden();
    }
}
