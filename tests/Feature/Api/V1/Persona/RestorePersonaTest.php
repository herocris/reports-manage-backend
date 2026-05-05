<?php

namespace Tests\Feature\Api\V1\Persona;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted persona records.
 */
class RestorePersonaTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_persona(): void
    {
        $actor = $this->createAuthorizedUser(['personas.restore']);
        $persona = Persona::factory()->create();
        $persona->delete();

        $this->actingAs($actor)->postJson("/api/v1/personas/{$persona->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Persona restored successfully.']);

        $this->assertNotSoftDeleted($persona);
    }

    public function test_user_without_permission_cannot_restore_persona(): void
    {
        $actor = User::factory()->create();
        $persona = Persona::factory()->create();
        $persona->delete();

        $this->actingAs($actor)->postJson("/api/v1/personas/{$persona->id}/restore")
            ->assertForbidden();
    }
}
