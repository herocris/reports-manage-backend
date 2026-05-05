<?php

namespace Tests\Feature\Api\V1\Adjunto;

use App\Models\Bien;
use App\Models\Informe;
use App\Models\Persona;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for uploading new adjunto files.
 */
class StoreAdjuntoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_upload_multiple_adjuntos_for_informe(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.create']);
        $informe = Informe::factory()->create();
        $archivoUno = UploadedFile::fake()->image('informe-1.jpg');
        $archivoDos = UploadedFile::fake()->image('informe-2.png');

        $response = $this->actingAs($actor)->post('/api/v1/adjuntos', [
            'tipo' => 'informe',
            'modelo_id' => $informe->id,
            'archivos' => [$archivoUno, $archivoDos],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Adjuntos created successfully.')
            ->assertJsonCount(2, 'data');

        $this->assertCount(2, $informe->fresh()->adjuntos);
        Storage::disk('public')->assertExists($informe->fresh()->adjuntos[0]->url);
        Storage::disk('public')->assertExists($informe->fresh()->adjuntos[1]->url);
    }

    public function test_authenticated_user_can_upload_adjunto_for_persona(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.create']);
        $persona = Persona::factory()->create();
        $archivo = UploadedFile::fake()->image('persona.jpg');

        $response = $this->actingAs($actor)->post('/api/v1/adjuntos', [
            'tipo' => 'persona',
            'modelo_id' => $persona->id,
            'archivos' => [$archivo],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.0.descripcion', 'persona.jpg');

        $this->assertCount(1, $persona->fresh()->adjuntos);
        $this->assertSame(Persona::class, $persona->fresh()->adjuntos->first()->adjuntable_type);
    }

    public function test_authenticated_user_can_upload_adjunto_for_bien(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.create']);
        $bien = Bien::factory()->create();
        $archivo = UploadedFile::fake()->image('bien.jpg');

        $response = $this->actingAs($actor)->post('/api/v1/adjuntos', [
            'tipo' => 'bien',
            'modelo_id' => $bien->id,
            'archivos' => [$archivo],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.0.descripcion', 'bien.jpg');

        $this->assertCount(1, $bien->fresh()->adjuntos);
        $this->assertSame(Bien::class, $bien->fresh()->adjuntos->first()->adjuntable_type);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.create']);

        $this->actingAs($actor)->post('/api/v1/adjuntos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tipo', 'modelo_id', 'archivos']);
    }

    public function test_store_validates_tipo(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.create']);
        $informe = Informe::factory()->create();
        $archivo = UploadedFile::fake()->image('test.jpg');

        $this->actingAs($actor)->post('/api/v1/adjuntos', [
            'tipo' => 'otro',
            'modelo_id' => $informe->id,
            'archivos' => [$archivo],
        ])->assertUnprocessable()->assertJsonValidationErrors(['tipo']);
    }

    public function test_store_validates_image_files(): void
    {
        Storage::fake('public');

        $actor = $this->createAuthorizedUser(['adjuntos.create']);
        $informe = Informe::factory()->create();
        $archivo = UploadedFile::fake()->create('archivo.pdf', 200, 'application/pdf');

        $this->actingAs($actor)->post('/api/v1/adjuntos', [
            'tipo' => 'informe',
            'modelo_id' => $informe->id,
            'archivos' => [$archivo],
        ])->assertUnprocessable()->assertJsonValidationErrors(['archivos.0']);
    }
}
