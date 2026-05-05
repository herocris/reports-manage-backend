<?php

namespace Tests\Feature\Api\V1\Informe;

use App\Models\Informe;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for attaching and detaching personas
 * from an informe's many-to-many relationship.
 */
class InformePersonasTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_can_attach_existing_persona_to_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $persona = Persona::factory()->create();

        $this->actingAs($actor)
            ->postJson("/api/v1/informes/{$informe->id}/personas", ['persona_id' => $persona->id])
            ->assertOk()
            ->assertJsonPath('message', 'Persona vinculada al informe exitosamente.');

        $this->assertTrue($informe->personas()->where('persona_id', $persona->id)->exists());
    }

    public function test_cannot_attach_persona_already_linked_to_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $persona = Persona::factory()->create();
        $informe->personas()->attach($persona->id);

        $this->actingAs($actor)
            ->postJson("/api/v1/informes/{$informe->id}/personas", ['persona_id' => $persona->id])
            ->assertStatus(422);
    }

    public function test_can_detach_persona_from_informe(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $persona = Persona::factory()->create();
        $informe->personas()->attach($persona->id);

        $this->actingAs($actor)
            ->deleteJson("/api/v1/informes/{$informe->id}/personas/{$persona->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Persona desvinculada del informe exitosamente.');

        $this->assertFalse($informe->personas()->where('persona_id', $persona->id)->exists());
        $this->assertModelExists($persona);
    }

    public function test_detach_persona_does_not_delete_the_persona(): void
    {
        $actor = $this->createAuthorizedUser(['informes.update']);
        $informe = Informe::factory()->create();
        $persona = Persona::factory()->create();
        $informe->personas()->attach($persona->id);

        $this->actingAs($actor)
            ->deleteJson("/api/v1/informes/{$informe->id}/personas/{$persona->id}")
            ->assertOk();

        $this->assertModelExists($persona);
        $this->assertNotSoftDeleted($persona);
    }

    public function test_attach_persona_requires_permission(): void
    {
        $actor = User::factory()->create();
        $informe = Informe::factory()->create();
        $persona = Persona::factory()->create();

        $this->actingAs($actor)
            ->postJson("/api/v1/informes/{$informe->id}/personas", ['persona_id' => $persona->id])
            ->assertForbidden();
    }
}
