<?php

namespace Tests\Feature\Api\V1\Institucion;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted institucion records.
 */
class RestoreInstitucionTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_institucion(): void
    {
        $actor = $this->createAuthorizedUser(['instituciones.restore']);
        $institucion = Institucion::factory()->create();
        $institucion->delete();

        $this->actingAs($actor)->postJson("/api/v1/instituciones/{$institucion->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Institucion restored successfully.']);

        $this->assertNotSoftDeleted($institucion);
    }

    public function test_user_without_permission_cannot_restore_institucion(): void
    {
        $actor = User::factory()->create();
        $institucion = Institucion::factory()->create();
        $institucion->delete();

        $this->actingAs($actor)->postJson("/api/v1/instituciones/{$institucion->id}/restore")
            ->assertForbidden();
    }
}
