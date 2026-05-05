<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Institucion\IndexInstitucionRequest;
use App\Http\Requests\Api\V1\Institucion\StoreInstitucionRequest;
use App\Http\Requests\Api\V1\Institucion\UpdateInstitucionRequest;
use App\Http\Resources\Api\V1\InstitucionResource;
use App\Models\Institucion;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\InstitucionIndexDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for Institucion (institution) records.
 *
 * An Institucion represents an organization or governmental body that
 * employs users and is associated with audit informes. Users are linked
 * to an institution via `institucion_id` on the `users` table.
 */
class InstitucionController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of instituciones.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see InstitucionIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexInstitucionRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<\Illuminate\Contracts\Pagination\LengthAwarePaginator<InstitucionResource>>
     */
    public function index(IndexInstitucionRequest $request): AnonymousResourceCollection
    {
        $instituciones = $this->applyIndexQuery(
            Institucion::query(),
            $request->validated(),
            InstitucionIndexDefinition::definition(),
        );

        return InstitucionResource::collection($instituciones)->additional([
            'success' => true,
            'message' => 'Instituciones retrieved successfully.',
        ]);
    }

    /**
     * Persist a new institucion record.
     *
     * @param  StoreInstitucionRequest  $request  Validated creation payload.
     * @return JsonResponse  201 with the created {@see InstitucionResource} envelope.
     */
    public function store(StoreInstitucionRequest $request): JsonResponse
    {
        $institucion = Institucion::create($request->validated());

        return (new InstitucionResource($institucion))
            ->additional([
                'success' => true,
                'message' => 'Institucion created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single institucion by its primary key.
     *
     * @param  Institucion  $institucion  The resolved Institucion model instance.
     * @return InstitucionResource  200 with the resource envelope.
     */
    public function show(Institucion $institucion): InstitucionResource
    {
        return (new InstitucionResource($institucion))->additional([
            'success' => true,
            'message' => 'Institucion retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given institucion.
     *
     * @param  UpdateInstitucionRequest  $request     Validated update payload.
     * @param  Institucion               $institucion  The resolved Institucion model instance.
     * @return InstitucionResource  200 with the updated resource envelope.
     */
    public function update(UpdateInstitucionRequest $request, Institucion $institucion): InstitucionResource
    {
        $institucion->update($request->validated());

        return (new InstitucionResource($institucion))->additional([
            'success' => true,
            'message' => 'Institucion updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given institucion.
     *
     * Sets `deleted_at`; users linked to this institution retain their
     * `institucion_id` FK but may need reassignment before restore.
     *
     * @param  Institucion  $institucion  The resolved Institucion model instance.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function destroy(Institucion $institucion): JsonResponse
    {
        $institucion->delete();

        return ApiResponse::success(null, 'Institucion deleted successfully.');
    }

    /**
     * Restore a soft-deleted institucion.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Institucion  $institucion  The soft-deleted Institucion model instance to restore.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function restore(Institucion $institucion): JsonResponse
    {
        $institucion->restore();

        return ApiResponse::success(null, 'Institucion restored successfully.');
    }
}
