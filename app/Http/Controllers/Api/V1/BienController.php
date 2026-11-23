<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Bien\IndexBienRequest;
use App\Http\Requests\Api\V1\Bien\StoreBienRequest;
use App\Http\Requests\Api\V1\Bien\UpdateBienRequest;
use App\Http\Resources\Api\V1\BienResource;
use App\Models\Bien;
use App\Models\Informe;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\BienIndexDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for Bien (asset) records.
 *
 * A Bien is a physical or intangible asset subject to audit. When created,
 * the `informe_id` from the request is used to attach the new bien to the
 * informe via the `bien_informe` pivot table. The `informe_id` is not stored
 * directly on the bien; the relationship is managed through the pivot.
 */
class BienController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of bienes.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see BienIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexBienRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<BienResource>>
     */
    public function index(IndexBienRequest $request): AnonymousResourceCollection
    {
        $bienes = $this->applyIndexQuery(
            Bien::query(),
            $request->validated(),
            BienIndexDefinition::definition(),
        );

        return BienResource::collection($bienes)->additional([
            'success' => true,
            'message' => 'Bienes retrieved successfully.',
        ]);
    }

    /**
     * Create a new bien and attach it to the specified informe.
     *
     * Resolves the `informe_id` via `findOrFail` (throws 404 if not found),
     * creates the bien record, then inserts a pivot row in `bien_informe`.
     * The `informe_id` field is stripped from the bien's own attributes.
     *
     * @param  StoreBienRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see BienResource} envelope.
     */
    public function store(StoreBienRequest $request): JsonResponse
    {
        $validated = $request->validated();
        // $informe = Informe::query()->findOrFail($validated['informe_id']);

        // unset($validated['informe_id']);

        $bien = Bien::create($validated);
        // $informe->bienes()->attach($bien->id);

        return (new BienResource($bien))
            ->additional([
                'success' => true,
                'message' => 'Bien created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single bien by its primary key.
     *
     * @param  Bien  $bien  The resolved Bien model instance.
     * @return BienResource 200 with the resource envelope.
     */
    public function show(Bien $bien): BienResource
    {
        return (new BienResource($bien))->additional([
            'success' => true,
            'message' => 'Bien retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given bien.
     *
     * @param  UpdateBienRequest  $request  Validated update payload.
     * @param  Bien  $bien  The resolved Bien model instance.
     * @return BienResource 200 with the updated resource envelope.
     */
    public function update(UpdateBienRequest $request, Bien $bien): BienResource
    {
        $bien->update($request->validated());

        return (new BienResource($bien))->additional([
            'success' => true,
            'message' => 'Bien updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given bien.
     *
     * Sets `deleted_at`; pivot rows in `bien_informe` are NOT removed.
     * The bien can be recovered via {@see restore()}.
     *
     * @param  Bien  $bien  The resolved Bien model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Bien $bien): JsonResponse
    {
        $bien->delete();

        return ApiResponse::success(null, 'Bien deleted successfully.');
    }

    /**
     * Restore a soft-deleted bien.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Bien  $bien  The soft-deleted Bien model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Bien $bien): JsonResponse
    {
        $bien->restore();

        return ApiResponse::success(null, 'Bien restored successfully.');
    }
}
