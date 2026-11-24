<?php

namespace Tests\Feature\Api\V1\Informe;

use App\Actions\Informe\CreateInformeAction;
use App\Actions\Informe\DeleteInformeAction;
use App\Actions\Informe\RestoreInformeAction;
use App\Actions\Informe\UpdateInformeAction;
use App\Models\ActivityLog;
use App\Models\Adjunto;
use App\Models\Ambito;
use App\Models\Bitacora;
use App\Models\Informe;
use App\Models\Observacion;
use App\Models\Producto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class InformeTransactionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_create_rolls_back_the_informe_and_audit_when_ambito_sync_fails(): void
    {
        $author = User::factory()->create();
        $producto = Producto::factory()->create();
        $auditCount = ActivityLog::query()->count();

        $this->assertThrows(fn () => $this->app->make(CreateInformeAction::class)->execute([
            'expediente' => 'EXP-TXN123',
            'titulo' => 'Creación atómica',
            'producto_id' => $producto->id,
            'fecha_inicio' => '2026-01-01',
            'fecha_final' => '2026-01-31',
            'ambitos' => [999999],
        ], $author), QueryException::class);

        $this->assertDatabaseMissing('informes', ['expediente' => 'EXP-TXN123']);
        $this->assertSame($auditCount, ActivityLog::query()->count());
    }

    public function test_update_rolls_back_attributes_and_existing_links_when_ambito_sync_fails(): void
    {
        $informe = Informe::factory()->create();
        $ambito = Ambito::factory()->create();
        $informe->ambitos()->attach($ambito);
        $originalTitle = $informe->titulo;
        $auditCount = ActivityLog::query()->count();

        $this->assertThrows(fn () => $this->app->make(UpdateInformeAction::class)->execute($informe, [
            'titulo' => 'Este cambio debe revertirse',
            'ambitos' => [999999],
        ]), QueryException::class);

        $this->assertSame($originalTitle, $informe->fresh()->titulo);
        $this->assertSame([$ambito->id], $informe->ambitos()->pluck('ambitos.id')->all());
        $this->assertSame($auditCount, ActivityLog::query()->count());
    }

    public function test_delete_rolls_back_all_children_and_audit_when_a_child_delete_fails(): void
    {
        $informe = Informe::factory()->create();
        $children = $this->createChildren($informe);
        $auditCount = ActivityLog::query()->count();

        Event::listen('eloquent.deleted: '.Bitacora::class, function (): void {
            throw new RuntimeException('Falló el borrado del hijo.');
        });

        $this->assertThrows(
            fn () => $this->app->make(DeleteInformeAction::class)->execute($informe),
            RuntimeException::class,
        );

        foreach ([$informe, ...$children] as $model) {
            $this->assertNotSoftDeleted($model);
            $this->assertNull($model->fresh()->deletion_batch_id);
        }

        $this->assertSame($auditCount, ActivityLog::query()->count());
    }

    public function test_restore_rolls_back_all_children_and_audit_when_a_child_restore_fails(): void
    {
        $informe = Informe::factory()->create();
        $children = $this->createChildren($informe);
        $this->app->make(DeleteInformeAction::class)->execute($informe);
        $batchId = $informe->fresh()->deletion_batch_id;
        $auditCount = ActivityLog::query()->count();

        Event::listen('eloquent.restored: '.Bitacora::class, function (): void {
            throw new RuntimeException('Falló la restauración del hijo.');
        });

        $this->assertThrows(
            fn () => $this->app->make(RestoreInformeAction::class)->execute($informe),
            RuntimeException::class,
        );

        foreach ([$informe, ...$children] as $model) {
            $this->assertSoftDeleted($model);
            $this->assertSame($batchId, $model->fresh()->deletion_batch_id);
        }

        $this->assertSame($auditCount, ActivityLog::query()->count());
    }

    /** @return array<int, Model> */
    private function createChildren(Informe $informe): array
    {
        return [
            Ubicacion::factory()->create(['informe_id' => $informe->id]),
            Observacion::factory()->create(['informe_id' => $informe->id]),
            Bitacora::factory()->create(['informe_id' => $informe->id]),
            Adjunto::factory()->create([
                'adjuntable_id' => $informe->id,
                'adjuntable_type' => Informe::class,
            ]),
        ];
    }
}
