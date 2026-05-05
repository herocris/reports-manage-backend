<?php

namespace Tests\Feature\Api\V1;

use App\Models\Adjunto;
use App\Models\Informe;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

/**
 * Contract tests freeze the public JSON shape of each API resource.
 * They catch silent breaking changes when Resources are refactored.
 */
/**
 * Contract tests verifying that every index endpoint returns
 * the standard envelope shape: data[], links, and meta keys.
 */
class ContractTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    // ─── Adjunto ──────────────────────────────────────────────────────────────

    public function test_adjunto_resource_exposes_expected_fields(): void
    {
        $actor = $this->createAuthorizedUser(['adjuntos.view']);
        $informe = Informe::factory()->create();
        $adjunto = Adjunto::factory()->create([
            'adjuntable_id' => $informe->id,
            'adjuntable_type' => Informe::class,
        ]);

        $this->actingAs($actor)
            ->getJson("/api/v1/adjuntos/{$adjunto->id}")
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'url',
                    'descripcion',
                    'adjuntable_id',
                    'adjuntable_type',
                    'download_url',
                    'stream_url',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    // ─── Informe ──────────────────────────────────────────────────────────────

    public function test_informe_resource_exposes_expected_fields(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $informe = Informe::factory()->create();

        $this->actingAs($actor)
            ->getJson("/api/v1/informes/{$informe->id}")
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'expediente',
                    'titulo',
                    'producto_id',
                    'fecha_inicio',
                    'fecha_final',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_informe_index_returns_paginated_envelope(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        Informe::factory()->count(3)->create();

        $this->actingAs($actor)
            ->getJson('/api/v1/informes')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total', 'last_page', 'from', 'to'],
            ]);
    }

    // ─── User ─────────────────────────────────────────────────────────────────

    public function test_user_resource_exposes_expected_fields(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);

        $this->actingAs($actor)
            ->getJson("/api/v1/users/{$actor->id}")
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'institucion_id',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_user_resource_exposes_email_verified_at_on_own_profile(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);

        $data = $this->actingAs($actor)
            ->getJson("/api/v1/users/{$actor->id}")
            ->assertOk()
            ->json('data');

        $this->assertArrayHasKey('email_verified_at', $data);
    }

    public function test_user_resource_hides_email_verified_at_on_other_user_profiles(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        $other = User::factory()->create();

        $data = $this->actingAs($actor)
            ->getJson("/api/v1/users/{$other->id}")
            ->assertOk()
            ->json('data');

        $this->assertArrayNotHasKey('email_verified_at', $data);
    }

    public function test_me_endpoint_always_exposes_email_verified_at(): void
    {
        $actor = User::factory()->create();

        $data = $this->actingAs($actor)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->json('data');

        $this->assertArrayHasKey('email_verified_at', $data);
    }

    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_mobile_login_response_exposes_token_and_user(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user' => ['id', 'name', 'email']],
            ]);
    }
}
