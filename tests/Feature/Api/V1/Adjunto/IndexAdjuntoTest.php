<?php

namespace Tests\Feature\Api\V1\Adjunto;

use App\Models\Adjunto;
use App\Models\Bien;
use App\Models\Informe;
use App\Models\Persona;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering adjunto records.
 */
class IndexAdjuntoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_adjuntos(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $informe = Informe::factory()->create();
        Adjunto::factory()->count(2)->create([
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)->getJson('/api/v1/adjuntos')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_authenticated_user_can_filter_adjuntos_by_informe_id(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $targetInforme = Informe::factory()->create();
        $otherInforme = Informe::factory()->create();

        $matchingAdjunto = Adjunto::factory()->create([
            'adjuntable_id' => $targetInforme->id,
            'adjuntable_type' => Informe::class,
        ]);

        Adjunto::factory()->create([
            'adjuntable_id' => $otherInforme->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)
            ->getJson("/api/v1/adjuntos?filter[informe_id]={$targetInforme->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingAdjunto->id);
    }

    public function test_authenticated_user_can_filter_adjuntos_by_persona_id(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $targetPersona = Persona::factory()->create();
        $otherPersona = Persona::factory()->create();

        $matchingAdjunto = Adjunto::factory()->create([
            'adjuntable_id' => $targetPersona->id,
            'adjuntable_type' => Persona::class,
        ]);

        Adjunto::factory()->create([
            'adjuntable_id' => $otherPersona->id,
            'adjuntable_type' => Persona::class,
        ]);

        $this->actingAs($actor)
            ->getJson("/api/v1/adjuntos?filter[persona_id]={$targetPersona->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingAdjunto->id);
    }

    public function test_authenticated_user_can_filter_adjuntos_by_bien_id(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $targetBien = Bien::factory()->create();
        $otherBien = Bien::factory()->create();

        $matchingAdjunto = Adjunto::factory()->create([
            'adjuntable_id' => $targetBien->id,
            'adjuntable_type' => Bien::class,
        ]);

        Adjunto::factory()->create([
            'adjuntable_id' => $otherBien->id,
            'adjuntable_type' => Bien::class,
        ]);

        $this->actingAs($actor)
            ->getJson("/api/v1/adjuntos?filter[bien_id]={$targetBien->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingAdjunto->id);
    }
}
