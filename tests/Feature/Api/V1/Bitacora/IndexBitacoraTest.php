<?php

namespace Tests\Feature\Api\V1\Bitacora;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering bitacora records.
 */
class IndexBitacoraTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_bitacoras(): void
    {
        $actor = $this->createAuthorizedUser(['bitacoras.view']);
        Bitacora::factory()->count(2)->create();

        $this->actingAs($actor)->getJson('/api/v1/bitacoras')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_bitacoras(): void
    {
        $actor = User::factory()->create();

        $this->getJson('/api/v1/bitacoras')->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/bitacoras')->assertForbidden();
    }
}
