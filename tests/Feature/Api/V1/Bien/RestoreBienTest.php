<?php

namespace Tests\Feature\Api\V1\Bien;

use App\Models\Bien;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Feature tests for restoring soft-deleted bien records.
 */
class RestoreBienTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_authorized_user_can_restore_a_deleted_bien(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.restore']);
        $bien = Bien::factory()->create();
        $bien->delete();

        $this->actingAs($actor)->postJson("/api/v1/bienes/{$bien->id}/restore")
            ->assertOk()
            ->assertJson(['message' => 'Bien restored successfully.']);

        $this->assertNotSoftDeleted($bien);
    }

    public function test_user_without_permission_cannot_restore_bien(): void
    {
        $actor = User::factory()->create();
        $bien = Bien::factory()->create();
        $bien->delete();

        $this->actingAs($actor)->postJson("/api/v1/bienes/{$bien->id}/restore")
            ->assertForbidden();
    }
}
