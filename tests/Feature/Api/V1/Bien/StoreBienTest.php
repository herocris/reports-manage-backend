<?php

namespace Tests\Feature\Api\V1\Bien;

use App\Models\Bien;
use App\Models\Informe;
use App\Models\TipoBien;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new bien records.
 */
class StoreBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_bien(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.create']);
        $informe = Informe::factory()->create();
        $tipoBien = TipoBien::factory()->create();

        $response = $this->actingAs($actor)->postJson('/api/v1/bienes', [
            'descripcion' => 'Laptop Dell',
            'tipobien_id' => $tipoBien->id,
            'informe_id' => $informe->id,
        ]);

        $response->assertCreated()->assertJsonFragment(['descripcion' => 'Laptop Dell']);

        $bien = Bien::query()->where('descripcion', 'Laptop Dell')->firstOrFail();
        $this->assertTrue($informe->bienes()->where('biens.id', $bien->id)->exists());
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.create']);

        $this->actingAs($actor)->postJson('/api/v1/bienes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['descripcion', 'tipobien_id', 'informe_id']);
    }

    public function test_store_validates_tipobien_exists(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.create']);

        $this->actingAs($actor)->postJson('/api/v1/bienes', [
            'descripcion' => 'Test bien',
            'tipobien_id' => 999999,
            'informe_id' => 999999,
        ])->assertUnprocessable()->assertJsonValidationErrors(['tipobien_id', 'informe_id']);
    }

    public function test_bienes_cannot_be_created_for_deleted_informes(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.create']);
        $informe = Informe::factory()->create();
        $tipoBien = TipoBien::factory()->create();
        $informe->delete();

        $this->actingAs($actor)->postJson('/api/v1/bienes', [
            'descripcion' => 'Laptop Dell',
            'tipobien_id' => $tipoBien->id,
            'informe_id' => $informe->id,
        ])->assertNotFound();

        $this->assertSame(0, Bien::query()->count());
    }

    public function test_failed_creation_rolls_back_the_bien(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.create']);
        $informe = Informe::factory()->create();
        $tipoBien = TipoBien::factory()->create();
        Event::listen('eloquent.created: '.Bien::class, function (Bien $bien): never {
            throw new RuntimeException('Simulated failure after insert.');
        });

        $this->actingAs($actor)->postJson('/api/v1/bienes', [
            'descripcion' => 'Laptop Dell',
            'tipobien_id' => $tipoBien->id,
            'informe_id' => $informe->id,
        ])->assertInternalServerError();

        $this->assertSame(0, Bien::query()->count());
        $this->assertSame(0, $informe->bienes()->count());
    }
}
