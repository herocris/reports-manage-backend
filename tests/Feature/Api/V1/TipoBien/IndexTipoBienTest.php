<?php

namespace Tests\Feature\Api\V1\TipoBien;

use App\Models\TipoBien;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for listing and filtering tipo de bien records.
 */
class IndexTipoBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authenticated_user_can_list_tipos_bien(): void
    {
        $actor = $this->createAuthorizedUser(['tipos_bien.view']);
        TipoBien::factory()->count(2)->create();

        $this->actingAs($actor)->getJson('/api/v1/tipos-bien')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_unauthenticated_or_forbidden_user_cannot_manage_tipos_bien(): void
    {
        $tipoBien = TipoBien::factory()->create();
        $actor = User::factory()->create();

        $this->getJson('/api/v1/tipos-bien')->assertUnauthorized();
        $this->getJson("/api/v1/tipos-bien/{$tipoBien->id}")->assertUnauthorized();
        $this->actingAs($actor)->getJson('/api/v1/tipos-bien')->assertForbidden();
    }
}
