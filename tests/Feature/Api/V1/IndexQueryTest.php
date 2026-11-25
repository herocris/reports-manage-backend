<?php

namespace Tests\Feature\Api\V1;

use App\Models\Bien;
use App\Models\Informe;
use App\Models\Institucion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TipoBien;
use App\Models\User;
use App\Support\Query\Definitions\RoleIndexDefinition;
use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesAuthorizedUser;
use Tests\TestCase;

class IndexQueryTest extends TestCase
{
    use CreatesAuthorizedUser, LazilyRefreshDatabase;

    public function test_informes_can_be_filtered_by_producto_id(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $matching = Informe::factory()->create();
        Informe::factory()->create();

        $this->actingAs($actor)
            ->getJson('/api/v1/informes?filter[producto_id]='.$matching->producto_id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
    }

    public function test_bienes_can_be_filtered_by_tipobien_id(): void
    {
        $actor = $this->createAuthorizedUser(['bienes.view']);
        $type = TipoBien::factory()->create();
        $matching = Bien::factory()->create(['tipobien_id' => $type->id]);
        Bien::factory()->create();

        $this->actingAs($actor)
            ->getJson('/api/v1/bienes?filter[tipobien_id]='.$type->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
    }

    public function test_sparse_fields_preserve_keys_needed_to_load_relationships(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $informe = Informe::factory()->create();

        $this->actingAs($actor)
            ->getJson('/api/v1/informes?fields=id,titulo&include=producto,usuarioCreador')
            ->assertOk()
            ->assertJsonPath('data.0.producto.id', $informe->producto_id)
            ->assertJsonPath('data.0.creador.id', $informe->usuariocreador_id)
            ->assertJsonMissingPath('data.0.expediente');
    }

    public function test_index_parameters_share_csv_case_and_boolean_normalization(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $informe = Informe::factory()->create();
        $informe->delete();
        Informe::factory()->create();

        $parameters = http_build_query([
            'include' => ' producto , usuarioCreador ',
            'fields' => ' id , titulo ',
            'direction' => 'ASC',
            'only_trashed' => 'true',
        ]);

        $this->actingAs($actor)
            ->getJson('/api/v1/informes?'.$parameters)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $informe->id)
            ->assertJsonPath('data.0.producto.id', $informe->producto_id)
            ->assertJsonPath('data.0.creador.id', $informe->usuariocreador_id);
    }

    public function test_sparse_fields_preserve_count_subqueries_and_their_bindings(): void
    {
        $role = Role::findOrCreate('counted', 'sanctum');
        $matchingUser = User::factory()->create(['name' => 'Matching user']);
        $otherUser = User::factory()->create(['name' => 'Other user']);
        $matchingUser->assignRole($role);
        $otherUser->assignRole($role);

        $roles = app(IndexQueryApplier::class)->apply(
            Role::query()->withCount(['users' => fn (Builder $query) => $query->where('name', 'Matching user')]),
            ['fields' => ['id', 'name']],
            RoleIndexDefinition::definition(),
        );

        $this->assertSame(1, $roles->first()->users_count);
        $this->assertArrayNotHasKey('guard_name', $roles->first()->getAttributes());
    }

    public function test_pagination_has_a_stable_order_when_sort_values_are_equal(): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);
        $informes = Informe::factory()->count(3)->create(['created_at' => now()->startOfDay()]);

        $firstResponse = $this->actingAs($actor)
            ->getJson('/api/v1/informes?sort=created_at&direction=asc&per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $secondPage = $this->getJson($firstResponse->json('links.next'))
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(1, 'data')
            ->json('data');

        $this->assertSame($informes->modelKeys(), array_column([...$firstResponse->json('data'), ...$secondPage], 'id'));
    }

    public function test_sparse_user_fields_can_include_a_nullable_institucion(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        $institution = Institucion::factory()->create();
        $target = User::factory()->create(['institucion_id' => $institution->id]);

        $this->actingAs($actor)
            ->getJson('/api/v1/users?fields=id,name&include=institucion&filter[email]='.$target->email)
            ->assertOk()
            ->assertJsonPath('data.0.institucion.id', $institution->id)
            ->assertJsonMissingPath('data.0.email');

        $this->getJson('/api/v1/users?include=institucion&filter[email]='.$actor->email)
            ->assertOk()
            ->assertJsonPath('data.0.institucion', null)
            ->assertJsonPath('data.0.institucion_id', null);
    }

    public function test_user_permission_queries_do_not_grow_with_the_number_of_users(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        $permission = Permission::findOrCreate('reports.read', 'sanctum');
        $role = Role::findOrCreate('reader', 'sanctum');
        $role->givePermissionTo($permission);
        User::factory()->create()->assignRole($role);

        DB::enableQueryLog();

        try {
            $this->actingAs($actor)->getJson('/api/v1/users?include=roles&per_page=100')->assertOk();
            $initialQueries = count(DB::getQueryLog());

            User::factory()->count(5)->create()->each(fn (User $user) => $user->assignRole($role));
            DB::flushQueryLog();

            $this->getJson('/api/v1/users?include=roles&per_page=100')
                ->assertOk()
                ->assertJsonCount(7, 'data');

            $this->assertLessThanOrEqual($initialQueries, count(DB::getQueryLog()));
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    public function test_super_admin_permissions_match_actual_grants(): void
    {
        $actor = $this->createAuthorizedUser(['users.view']);
        $granted = Permission::findOrCreate('reports.granted', 'sanctum');
        Permission::findOrCreate('reports.unassigned', 'sanctum');
        $role = Role::findOrCreate('super-admin', 'sanctum');
        $role->givePermissionTo($granted);
        $target = User::factory()->create();
        $target->assignRole($role);

        $this->actingAs($actor)
            ->getJson('/api/v1/users?include=permissions&filter[email]='.$target->email)
            ->assertOk()
            ->assertJsonPath('data.0.permissions', ['reports.granted']);
    }

    /** @param array<string, mixed> $parameters */
    #[DataProvider('invalidIndexParameters')]
    public function test_index_parameters_are_consistently_validated(array $parameters, string $error): void
    {
        $actor = $this->createAuthorizedUser(['informes.view']);

        $this->actingAs($actor)
            ->getJson('/api/v1/informes?'.http_build_query($parameters))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($error);
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidIndexParameters(): array
    {
        return [
            'unknown filter' => [['filter' => ['unsupported' => 'value']], 'filter'],
            'unknown include' => [['include' => 'unsupported'], 'include.0'],
            'unknown field' => [['fields' => 'password'], 'fields.0'],
            'invalid boolean' => [['only_trashed' => 'invalid'], 'only_trashed'],
            'invalid sort direction' => [['direction' => ['desc']], 'direction'],
            'oversized page' => [['per_page' => 101], 'per_page'],
        ];
    }
}
