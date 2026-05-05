<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Observacion\IndexObservacionRequest;
use App\Http\Requests\Api\V1\Observacion\StoreObservacionRequest;
use App\Http\Requests\Api\V1\Observacion\UpdateObservacionRequest;
use App\Http\Resources\Api\V1\ObservacionResource;
use App\Models\Observacion;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\ObservacionIndexDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for Observacion records attached to Informes.
 *
 * An Observacion records a finding, comment, or remark made during the audit
 * process. It belongs directly to an Informe via `informe_id` and participates
 * in cascade soft-delete with its parent.
 */
class ObservacionController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of observaciones.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see ObservacionIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexObservacionRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<\Illuminate\Contracts\Pagination\LengthAwarePaginator<ObservacionResource>>
     */
    public function index(IndexObservacionRequest $request): AnonymousResourceCollection
    {
        $observaciones = $this->applyIndexQuery(
            Observacion::query(),
            $request->validated(),
            ObservacionIndexDefinition::definition(),
        );

        return ObservacionResource::collection($observaciones)->additional([
            'success' => true,
            'message' => 'Observaciones retrieved successfully.',
        ]);
    }

    /**
     * Persist a new observacion record.
     *
     * @param  StoreObservacionRequest  $request  Validated creation payload.
     * @return JsonResponse  201 with the created {@see ObservacionResource} envelope.
     */
    public function store(StoreObservacionRequest $request): JsonResponse
    {
        $observacion = Observacion::create($request->validated());

        return (new ObservacionResource($observacion))
            ->additional([
                'success' => true,
                'message' => 'Observacion created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single observacion by its primary key.
     *
     * @param  Observacion  $observacion  The resolved Observacion model instance.
     * @return ObservacionResource  200 with the resource envelope.
     */
    public function show(Observacion $observacion): ObservacionResource
    {
        return (new ObservacionResource($observacion))->additional([
            'success' => true,
            'message' => 'Observacion retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given observacion.
     *
     * @param  UpdateObservacionRequest  $request     Validated update payload.
     * @param  Observacion               $observacion  The resolved Observacion model instance.
     * @return ObservacionResource  200 with the updated resource envelope.
     */
    public function update(UpdateObservacionRequest $request, Observacion $observacion): ObservacionResource
    {
        $observacion->update($request->validated());

        return (new ObservacionResource($observacion))->additional([
            'success' => true,
            'message' => 'Observacion updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given observacion.
     *
     * Sets `deleted_at`; the record can be recovered via {@see restore()}.
     *
     * @param  Observacion  $observacion  The resolved Observacion model instance.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function destroy(Observacion $observacion): JsonResponse
    {
        $observacion->delete();

        return ApiResponse::success(null, 'Observacion deleted successfully.');
    }

    /**
     * Restore a soft-deleted observacion.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Observacion  $observacion  The soft-deleted Observacion model instance to restore.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function restore(Observacion $observacion): JsonResponse
    {
        $observacion->restore();

        return ApiResponse::success(null, 'Observacion restored successfully.');
    }
}
