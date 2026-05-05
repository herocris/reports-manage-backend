<?php

namespace Tests\Feature\Api\V1\Informe;

use App\Models\Bien;
use App\Models\Informe;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for attaching and detaching bienes
 * from an informe's many-to-many relationship.
 */
class InformeBienesTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_can_attach_existing_bien_to_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $bien = Bien::factory()->create();

        $this->actingAs($actor)
            ->postJson("/api/v1/informes/{$informe->id}/bienes", ['bien_id' => $bien->id])
            ->assertOk()
            ->assertJsonPath('message', 'Bien vinculado al informe exitosamente.');

        $this->assertTrue($informe->bienes()->where('bien_id', $bien->id)->exists());
    }

    public function test_cannot_attach_bien_already_linked_to_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $bien = Bien::factory()->create();
        $informe->bienes()->attach($bien->id);

        $this->actingAs($actor)
            ->postJson("/api/v1/informes/{$informe->id}/bienes", ['bien_id' => $bien->id])
            ->assertStatus(422);
    }

    public function test_can_detach_bien_from_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $bien = Bien::factory()->create();
        $informe->bienes()->attach($bien->id);

        $this->actingAs($actor)
            ->deleteJson("/api/v1/informes/{$informe->id}/bienes/{$bien->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Bien desvinculado del informe exitosamente.');

        $this->assertFalse($informe->bienes()->where('bien_id', $bien->id)->exists());
        $this->assertModelExists($bien);
    }

    public function test_detach_bien_does_not_delete_the_bien(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $bien = Bien::factory()->create();
        $informe->bienes()->attach($bien->id);

        $this->actingAs($actor)
            ->deleteJson("/api/v1/informes/{$informe->id}/bienes/{$bien->id}")
            ->assertOk();

        $this->assertModelExists($bien);
        $this->assertNotSoftDeleted($bien);
    }

    public function test_attach_bien_requires_permission(): void
    {
        $actor = User::factory()->create();
        $informe = Informe::factory()->create();
        $bien = Bien::factory()->create();

        $this->actingAs($actor)
            ->postJson("/api/v1/informes/{$informe->id}/bienes", ['bien_id' => $bien->id])
            ->assertForbidden();
    }
}
