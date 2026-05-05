<?php

namespace Tests\Feature\Api\V1\Adjunto;

use App\Models\Adjunto;
use App\Models\Informe;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for retrieving, updating, soft-deleting,
 * downloading, and streaming adjunto files.
 */
class ShowUpdateDestroyAdjuntoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_view_update_and_delete_adjunto(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.view', 'adjuntos.update', 'adjuntos.delete']);
        $informe = Informe::factory()->create();
        $adjunto = Adjunto::factory()->create([
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)->getJson("/api/v1/adjuntos/{$adjunto->id}")->assertOk();
        $this->actingAs($actor)->putJson("/api/v1/adjuntos/{$adjunto->id}", ['descripcion' => 'Actualizado'])
            ->assertOk()->assertJsonFragment(['descripcion' => 'Actualizado']);
        $this->actingAs($actor)->deleteJson("/api/v1/adjuntos/{$adjunto->id}")->assertOk();

        $this->assertSoftDeleted($adjunto);
    }

    public function test_authenticated_user_can_download_adjunto(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $informe = Informe::factory()->create();
        $path = 'adjuntos/informe/prueba.jpg';
        Storage::disk('public')->put($path, 'contenido');

        $adjunto = Adjunto::factory()->create([
            'url' => $path,
            'descripcion' => 'evidencia.jpg',
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)
            ->get("/api/v1/adjuntos/{$adjunto->id}/download")
            ->assertOk()
            ->assertDownload('evidencia.jpg');
    }

    public function test_download_returns_404_when_file_is_missing(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $informe = Informe::factory()->create();
        $adjunto = Adjunto::factory()->create([
            'url' => 'adjuntos/informe/no-existe.jpg',
            'descripcion' => 'no-existe.jpg',
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)
            ->get("/api/v1/adjuntos/{$adjunto->id}/download")
            ->assertNotFound();
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_adjuntos(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/adjuntos')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/adjuntos')->assertForbidden();
    }

    public function test_show_returns_404_for_nonexistent_adjunto(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.view']);

        $this->actingAs($actor)->getJson('/api/v1/adjuntos/999999')->assertNotFound();
    }

    public function test_authenticated_user_can_stream_adjunto_inline(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $informe = Informe::factory()->create();
        $path = 'adjuntos/informe/imagen.jpg';
        Storage::disk('public')->put($path, 'contenido');

        $adjunto = Adjunto::factory()->create([
            'url' => $path,
            'descripcion' => 'imagen.jpg',
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)
            ->get("/api/v1/adjuntos/{$adjunto->id}/stream")
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=imagen.jpg');
    }

    public function test_stream_returns_404_when_file_is_missing(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $informe = Informe::factory()->create();
        $adjunto = Adjunto::factory()->create([
            'url' => 'adjuntos/informe/no-existe.jpg',
            'descripcion' => 'no-existe.jpg',
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)
            ->get("/api/v1/adjuntos/{$adjunto->id}/stream")
            ->assertNotFound();
    }

    public function test_physical_file_is_deleted_when_adjunto_is_force_deleted(): void
    {
        Storage::fake('public');

        $informe = Informe::factory()->create();
        $path = 'adjuntos/informe/documento.pdf';
        Storage::disk('public')->put($path, 'contenido');

        $adjunto = Adjunto::factory()->create([
            'url' => $path,
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $adjunto->forceDelete();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_physical_file_is_kept_when_adjunto_is_soft_deleted(): void
    {
        Storage::fake('public');

        $informe = Informe::factory()->create();
        $path = 'adjuntos/informe/documento.pdf';
        Storage::disk('public')->put($path, 'contenido');

        $adjunto = Adjunto::factory()->create([
            'url' => $path,
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $adjunto->delete();

        Storage::disk('public')->assertExists($path);
    }
}
