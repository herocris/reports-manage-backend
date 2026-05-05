<?php

namespace Tests\Feature\Api\V1\Bien;

use App\Models\Bien;
use App\Models\Informe;
use App\Models\TipoBien;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
}
