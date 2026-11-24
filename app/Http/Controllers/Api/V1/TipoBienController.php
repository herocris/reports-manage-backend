<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TipoBien\IndexTipoBienRequest;
use App\Http\Requests\Api\V1\TipoBien\StoreTipoBienRequest;
use App\Http\Requests\Api\V1\TipoBien\UpdateTipoBienRequest;
use App\Http\Resources\Api\V1\TipoBienResource;
use App\Models\TipoBien;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\TipoBienIndexDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for TipoBien (asset type) records.
 *
 * A TipoBien classifies bienes into categories (e.g. "inmueble", "vehículo").
 * It is soft-deletable; existing Bien records referencing a deleted TipoBien
 * continue to hold the FK and are not affected by the deletion.
 */
class TipoBienController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of tipos de bien.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see TipoBienIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexTipoBienRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<TipoBienResource>>
     */
    public function index(IndexTipoBienRequest $request): AnonymousResourceCollection
    {
        $tiposBien = $this->applyIndexQuery(
            TipoBien::query(),
            $request->validated(),
            TipoBienIndexDefinition::definition(),
        );

        return TipoBienResource::collection($tiposBien)->additional([
            'success' => true,
            'message' => 'Tipos de bien retrieved successfully.',
        ]);
    }

    /**
     * Persist a new tipo de bien record.
     *
     * @param  StoreTipoBienRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see TipoBienResource} envelope.
     */
    public function store(StoreTipoBienRequest $request): JsonResponse
    {
        $tipoBien = TipoBien::create($request->validated());

        return (new TipoBienResource($tipoBien))
            ->additional([
                'success' => true,
                'message' => 'Tipo de bien created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single tipo de bien by its primary key.
     *
     * @param  TipoBien  $tipoBien  The resolved TipoBien model instance.
     * @return TipoBienResource 200 with the resource envelope.
     */
    public function show(TipoBien $tipoBien): TipoBienResource
    {
        return (new TipoBienResource($tipoBien))->additional([
            'success' => true,
            'message' => 'Tipo de bien retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given tipo de bien.
     *
     * @param  UpdateTipoBienRequest  $request  Validated update payload.
     * @param  TipoBien  $tipoBien  The resolved TipoBien model instance.
     * @return TipoBienResource 200 with the updated resource envelope.
     */
    public function update(UpdateTipoBienRequest $request, TipoBien $tipoBien): TipoBienResource
    {
        $tipoBien->update($request->validated());

        return (new TipoBienResource($tipoBien))->additional([
            'success' => true,
            'message' => 'Tipo de bien updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given tipo de bien.
     *
     * Sets `deleted_at`; the record can be recovered via {@see restore()}.
     *
     * @param  TipoBien  $tipoBien  The resolved TipoBien model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(TipoBien $tipoBien): JsonResponse
    {
        $tipoBien->delete();

        return ApiResponse::success(null, 'Tipo de bien deleted successfully.');
    }

    /**
     * Restore a soft-deleted tipo de bien.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  TipoBien  $tipoBien  The soft-deleted TipoBien model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(TipoBien $tipoBien): JsonResponse
    {
        $tipoBien->restore();

        return ApiResponse::success(null, 'TipoBien restored successfully.');
    }
}
