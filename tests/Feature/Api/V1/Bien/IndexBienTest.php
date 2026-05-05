<?php

namespace Tests\Feature\Api\V1\Bien;

use App\Models\Bien;
use App\Models\Informe;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering bien records.
 */
class IndexBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_bienes(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.view']);
        $informe = Informe::factory()->create();
        $otrosBienes = Bien::factory()->count(2)->create();
        $informe->bienes()->attach($otrosBienes->pluck('id')->all());

        $this->actingAs($actor)->getJson("/api/v1/bienes?informe_id={$informe->id}")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_authenticated_user_can_filter_bienes_by_informe_id(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.view']);
        $targetInforme = Informe::factory()->create();
        $otherInforme = Informe::factory()->create();

        $matchingBien = Bien::factory()->create();
        $otherBien = Bien::factory()->create();

        $targetInforme->bienes()->attach($matchingBien->id);
        $otherInforme->bienes()->attach($otherBien->id);

        $this->actingAs($actor)
            ->getJson("/api/v1/bienes?filter[informe_id]={$targetInforme->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingBien->id);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_bienes(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/bienes')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/bienes?informe_id=1')->assertForbidden();
    }
}
