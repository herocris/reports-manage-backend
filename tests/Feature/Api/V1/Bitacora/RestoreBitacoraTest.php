<?php

namespace Tests\Feature\Api\V1\Bitacora;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted bitacora records.
 */
class RestoreBitacoraTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_bitacora(): void
    {
        $actor = $this->createAuthorizedUser(['bitacoras.restore']);
        $bitacora = Bitacora::factory()->create();
        $bitacora->delete();

        $this->actingAs($actor)->postJson("/api/v1/bitacoras/{$bitacora->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Bitacora restored successfully.']);

        $this->assertNotSoftDeleted($bitacora);
    }

    public function test_user_without_permission_cannot_restore_bitacora(): void
    {
        $actor = User::factory()->create();
        $bitacora = Bitacora::factory()->create();
        $bitacora->delete();

        $this->actingAs($actor)->postJson("/api/v1/bitacoras/{$bitacora->id}/restore")
            ->assertForbidden();
    }
}
