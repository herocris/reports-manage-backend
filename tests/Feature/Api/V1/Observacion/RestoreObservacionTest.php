<?php

namespace Tests\Feature\Api\V1\Observacion;

use App\Models\Observacion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted observacion records.
 */
class RestoreObservacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_observacion(): void
    {
        $actor = $this->createAuthorizedUser(['observaciones.restore']);
        $observacion = Observacion::factory()->create();
        $observacion->delete();

        $this->actingAs($actor)->postJson("/api/v1/observaciones/{$observacion->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Observacion restored successfully.']);

        $this->assertNotSoftDeleted($observacion);
    }

    public function test_user_without_permission_cannot_restore_observacion(): void
    {
        $actor = User::factory()->create();
        $observacion = Observacion::factory()->create();
        $observacion->delete();

        $this->actingAs($actor)->postJson("/api/v1/observaciones/{$observacion->id}/restore")
            ->assertForbidden();
    }
}
