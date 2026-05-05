<?php

namespace Tests\Feature\Api\V1\Ubicacion;

use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted ubicacion records.
 */
class RestoreUbicacionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_ubicacion(): void
    {
        $actor = $this->createAuthorizedUser(['ubicaciones.restore']);
        $ubicacion = Ubicacion::factory()->create();
        $ubicacion->delete();

        $this->actingAs($actor)->postJson("/api/v1/ubicaciones/{$ubicacion->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Ubicacion restored successfully.']);

        $this->assertNotSoftDeleted($ubicacion);
    }

    public function test_user_without_permission_cannot_restore_ubicacion(): void
    {
        $actor = User::factory()->create();
        $ubicacion = Ubicacion::factory()->create();
        $ubicacion->delete();

        $this->actingAs($actor)->postJson("/api/v1/ubicaciones/{$ubicacion->id}/restore")
            ->assertForbidden();
    }
}
