<?php

namespace Tests\Feature\Api\V1\Ambito;

use App\Models\Ambito;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering ambito records.
 */
class IndexAmbitoTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_ambitos(): void
    {
        $actor = $this->createAuthorizedUser(['ambitos.view']);
        Ambito::factory()->count(2)->create();

        $this->actingAs($actor)->getJson('/api/v1/ambitos')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_unauthenticated_user_cannot_manage_ambitos(): void
    {
        $ambito = Ambito::factory()->create();

        $this->getJson('/api/v1/ambitos')->assertUnauthorized();
        $this->getJson("/api/v1/ambitos/{$ambito->id}")->assertUnauthorized();
    }

    public function test_user_without_permissions_cannot_manage_ambitos(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)->getJson('/api/v1/ambitos')->assertForbidden();
    }
}
