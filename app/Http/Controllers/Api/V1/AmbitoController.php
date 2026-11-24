<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ambito\IndexAmbitoRequest;
use App\Http\Requests\Api\V1\Ambito\StoreAmbitoRequest;
use App\Http\Requests\Api\V1\Ambito\UpdateAmbitoRequest;
use App\Http\Resources\Api\V1\AmbitoResource;
use App\Models\Ambito;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\AmbitoIndexDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for Ambito (scope/domain area) records.
 *
 * An Ambito defines a thematic or geographic scope used to classify Informes.
 * The entity supports soft-delete; deleted ambitos can be listed via `only_trashed`
 * and restored without data loss.
 */
class AmbitoController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of ambitos.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see AmbitoIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexAmbitoRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<AmbitoResource>>
     */
    public function index(IndexAmbitoRequest $request): AnonymousResourceCollection
    {
        $ambitos = $this->applyIndexQuery(
            Ambito::query(),
            $request->validated(),
            AmbitoIndexDefinition::definition(),
        );

        return AmbitoResource::collection($ambitos)->additional([
            'success' => true,
            'message' => 'Ambitos retrieved successfully.',
        ]);
    }

    /**
     * Persist a new ambito record.
     *
     * @param  StoreAmbitoRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see AmbitoResource} envelope.
     */
    public function store(StoreAmbitoRequest $request): JsonResponse
    {
        $ambito = Ambito::create($request->validated());

        return (new AmbitoResource($ambito))
            ->additional([
                'success' => true,
                'message' => 'Ambito created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single ambito by its primary key.
     *
     * @param  Ambito  $ambito  The resolved Ambito model instance.
     * @return AmbitoResource 200 with the resource envelope.
     */
    public function show(Ambito $ambito): AmbitoResource
    {
        return (new AmbitoResource($ambito))->additional([
            'success' => true,
            'message' => 'Ambito retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given ambito.
     *
     * @param  UpdateAmbitoRequest  $request  Validated update payload.
     * @param  Ambito  $ambito  The resolved Ambito model instance.
     * @return AmbitoResource 200 with the updated resource envelope.
     */
    public function update(UpdateAmbitoRequest $request, Ambito $ambito): AmbitoResource
    {
        $ambito->update($request->validated());

        return (new AmbitoResource($ambito))->additional([
            'success' => true,
            'message' => 'Ambito updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given ambito.
     *
     * Sets `deleted_at`; the record can be recovered via {@see restore()}.
     *
     * @param  Ambito  $ambito  The resolved Ambito model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Ambito $ambito): JsonResponse
    {
        $ambito->delete();

        return ApiResponse::success(null, 'Ambito deleted successfully.');
    }

    /**
     * Restore a soft-deleted ambito.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Ambito  $ambito  The soft-deleted Ambito model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Ambito $ambito): JsonResponse
    {
        $ambito->restore();

        return ApiResponse::success(null, 'Ambito restored successfully.');
    }
}
