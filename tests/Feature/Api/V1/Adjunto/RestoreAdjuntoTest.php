<?php

namespace Tests\Feature\Api\V1\Adjunto;

use App\Models\Adjunto;
use App\Models\Informe;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted adjunto records.
 */
class RestoreAdjuntoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_adjunto(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.restore']);
        $informe = Informe::factory()->create();
        $adjunto = Adjunto::factory()->create([
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);
        $adjunto->delete();

        $this->actingAs($actor)->postJson("/api/v1/adjuntos/{$adjunto->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Adjunto restored successfully.']);

        $this->assertNotSoftDeleted($adjunto);
    }

    public function test_user_without_permission_cannot_restore_adjunto(): void
    {
        $actor = User::factory()->create();
        $informe = Informe::factory()->create();
        $adjunto = Adjunto::factory()->create([
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);
        $adjunto->delete();

        $this->actingAs($actor)->postJson("/api/v1/adjuntos/{$adjunto->id}/restore")
            ->assertForbidden();
    }
}
