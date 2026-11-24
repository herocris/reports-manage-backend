<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ubicacion\IndexUbicacionRequest;
use App\Http\Requests\Api\V1\Ubicacion\StoreUbicacionRequest;
use App\Http\Requests\Api\V1\Ubicacion\UpdateUbicacionRequest;
use App\Http\Resources\Api\V1\UbicacionResource;
use App\Models\Informe;
use App\Models\Ubicacion;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\UbicacionIndexDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for Ubicacion (location) records attached to Informes.
 *
 * A Ubicacion records a geographic address or reference point relevant to an
 * informe. It is created through the parent informe's `ubicaciones()` relation,
 * ensuring the `informe_id` FK is set implicitly. Participates in cascade
 * soft-delete with its parent Informe.
 */
class UbicacionController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of ubicaciones.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see UbicacionIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexUbicacionRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<UbicacionResource>>
     */
    public function index(IndexUbicacionRequest $request): AnonymousResourceCollection
    {
        $ubicaciones = $this->applyIndexQuery(
            Ubicacion::query(),
            $request->validated(),
            UbicacionIndexDefinition::definition(),
        );

        return UbicacionResource::collection($ubicaciones)->additional([
            'success' => true,
            'message' => 'Ubicaciones retrieved successfully.',
        ]);
    }

    /**
     * Create a new ubicacion and associate it with the specified informe.
     *
     * Resolves the `informe_id` via `findOrFail` (throws 404 if not found),
     * then creates the ubicacion through the informe's `ubicaciones()` relation
     * so the FK is set automatically. The `informe_id` field is stripped from
     * the ubicacion's own attributes before creation.
     *
     * @param  StoreUbicacionRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see UbicacionResource} envelope.
     */
    public function store(StoreUbicacionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $informe = Informe::query()->findOrFail($validated['informe_id']);

        unset($validated['informe_id']);

        $ubicacion = $informe->ubicaciones()->create($validated);

        return (new UbicacionResource($ubicacion))
            ->additional([
                'success' => true,
                'message' => 'Ubicacion created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single ubicacion by its primary key.
     *
     * @param  Ubicacion  $ubicacion  The resolved Ubicacion model instance.
     * @return UbicacionResource 200 with the resource envelope.
     */
    public function show(Ubicacion $ubicacion): UbicacionResource
    {
        return (new UbicacionResource($ubicacion))->additional([
            'success' => true,
            'message' => 'Ubicacion retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given ubicacion.
     *
     * @param  UpdateUbicacionRequest  $request  Validated update payload.
     * @param  Ubicacion  $ubicacion  The resolved Ubicacion model instance.
     * @return UbicacionResource 200 with the updated resource envelope.
     */
    public function update(UpdateUbicacionRequest $request, Ubicacion $ubicacion): UbicacionResource
    {
        $ubicacion->update($request->validated());

        return (new UbicacionResource($ubicacion))->additional([
            'success' => true,
            'message' => 'Ubicacion updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given ubicacion.
     *
     * Sets `deleted_at`; the record can be recovered via {@see restore()}.
     *
     * @param  Ubicacion  $ubicacion  The resolved Ubicacion model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Ubicacion $ubicacion): JsonResponse
    {
        $ubicacion->delete();

        return ApiResponse::success(null, 'Ubicacion deleted successfully.');
    }

    /**
     * Restore a soft-deleted ubicacion.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Ubicacion  $ubicacion  The soft-deleted Ubicacion model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Ubicacion $ubicacion): JsonResponse
    {
        $ubicacion->restore();

        return ApiResponse::success(null, 'Ubicacion restored successfully.');
    }
}
