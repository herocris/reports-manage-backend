<?php

namespace Tests\Feature\Api\V1\Informe;

use App\Models\Adjunto;
use App\Models\Bitacora;
use App\Models\Informe;
use App\Models\Observacion;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for soft-deleting and restoring informe records
 * and their cascaded child models.
 */
class DestroyRestoreInformeTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.restore']);
        $informe = Informe::factory()->create();
        $informe->delete();

        $this->actingAs($actor)->postJson("/api/v1/informes/{$informe->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Informe restored successfully.']);

        $this->assertNotSoftDeleted($informe);
    }

    public function test_destroy_and_restore_cascade_soft_delete_related_models(): void
    {
        $actor = $this->createAuthorizedUser(['informes.delete', 'informes.restore']);
        $informe = Informe::factory()->create();

        $ubicacion = Ubicacion::factory()->create(['informe_id' => $informe->id]);
        $observacion = Observacion::factory()->create(['informe_id' => $informe->id]);
        $bitacora = Bitacora::factory()->create(['informe_id' => $informe->id]);
        $adjunto = Adjunto::factory()->create([
            'adjuntable_id'   => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)->deleteJson("/api/v1/informes/{$informe->id}")
            ->assertOk();

        $this->assertSoftDeleted($informe);
        $this->assertSoftDeleted($ubicacion);
        $this->assertSoftDeleted($observacion);
        $this->assertSoftDeleted($bitacora);
        $this->assertSoftDeleted($adjunto);

        $this->actingAs($actor)->postJson("/api/v1/informes/{$informe->id}/restore")
            ->assertOk();

        $this->assertNotSoftDeleted($informe);
        $this->assertNotSoftDeleted($ubicacion);
        $this->assertNotSoftDeleted($observacion);
        $this->assertNotSoftDeleted($bitacora);
        $this->assertNotSoftDeleted($adjunto);
    }

    public function test_user_without_permission_cannot_restore_informe(): void
    {
        $actor = User::factory()->create();
        $informe = Informe::factory()->create();
        $informe->delete();

        $this->actingAs($actor)->postJson("/api/v1/informes/{$informe->id}/restore")
            ->assertForbidden();
    }
}
