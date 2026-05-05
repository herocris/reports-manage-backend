<?php

namespace Tests\Feature\Api\V1\TipoBien;

use App\Models\TipoBien;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted tipo de bien records.
 */
class RestoreTipoBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_tipo_bien(): void
    {
        $actor = $this->createAuthorizedUser(['tipos_bien.restore']);
        $tipoBien = TipoBien::factory()->create();
        $tipoBien->delete();

        $this->actingAs($actor)->postJson("/api/v1/tipos-bien/{$tipoBien->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'TipoBien restored successfully.']);

        $this->assertNotSoftDeleted($tipoBien);
    }

    public function test_user_without_permission_cannot_restore_tipo_bien(): void
    {
        $actor = User::factory()->create();
        $tipoBien = TipoBien::factory()->create();
        $tipoBien->delete();

        $this->actingAs($actor)->postJson("/api/v1/tipos-bien/{$tipoBien->id}/restore")
            ->assertForbidden();
    }
}
