<?php

namespace Tests\Feature\Api\V1\Ambito;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for creating new ambito records.
 */
class StoreAmbitoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_create_ambito(): void
    {
        $actor = $this->createAuthorizedUser(['ambitos.create']);

        $this->actingAs($actor)->postJson('/api/v1/ambitos', [
            'nombre' => 'Nacional',
            'descripcion' => 'Ambito nacional',
        ])->assertCreated()->assertJsonFragment(['nombre' => 'Nacional']);
    }

    public function test_store_validates_required_fields(): void
    {
        $actor = $this->createAuthorizedUser(['ambitos.create']);

        $this->actingAs($actor)->postJson('/api/v1/ambitos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'descripcion']);
    }
}
