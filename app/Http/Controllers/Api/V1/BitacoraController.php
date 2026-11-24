<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Bitacora\IndexBitacoraRequest;
use App\Http\Requests\Api\V1\Bitacora\StoreBitacoraRequest;
use App\Http\Requests\Api\V1\Bitacora\UpdateBitacoraRequest;
use App\Http\Resources\Api\V1\BitacoraResource;
use App\Models\Bitacora;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\BitacoraIndexDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD and restore operations for Bitacora (log entry) records.
 *
 * A bitacora represents a timestamped activity entry associated with a
 * specific Informe. All write operations require the corresponding
 * `bitacora.*` permissions enforced by route middleware.
 * Soft-deleted bitacoras are automatically cascade-deleted and restored
 * when their parent Informe is deleted or restored.
 */
class BitacoraController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of bitacoras.
     *
     * Delegates to {@see IndexQueryApplier} using {@see BitacoraIndexDefinition},
     * supporting search, sort, per-field filters, includes, field selection,
     * pagination, and an `only_trashed` soft-delete scope.
     *
     * @param  IndexBitacoraRequest  $request  Validated query parameters from the request.
     * @return AnonymousResourceCollection<LengthAwarePaginator<BitacoraResource>>
     */
    public function index(IndexBitacoraRequest $request): AnonymousResourceCollection
    {
        $bitacoras = $this->applyIndexQuery(
            Bitacora::query(),
            $request->validated(),
            BitacoraIndexDefinition::definition(),
        );

        return BitacoraResource::collection($bitacoras)->additional([
            'success' => true,
            'message' => 'Bitacoras retrieved successfully.',
        ]);
    }

    /**
     * Persist a new bitacora log entry and return it as a 201 response.
     *
     * The payload must include a `descripcion`, a `fecha`, and the parent
     * `informe_id`. The informe must exist and must not be soft-deleted.
     *
     * @param  StoreBitacoraRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see BitacoraResource} envelope.
     */
    public function store(StoreBitacoraRequest $request): JsonResponse
    {
        $bitacora = Bitacora::create($request->validated());

        return (new BitacoraResource($bitacora))
            ->additional([
                'success' => true,
                'message' => 'Bitacora created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single bitacora by its primary key.
     *
     * Route model binding resolves the `{bitacora}` segment; if the record
     * does not exist or is soft-deleted, Laravel automatically returns 404.
     *
     * @param  Bitacora  $bitacora  The resolved Bitacora model instance.
     * @return BitacoraResource 200 with the resource envelope.
     */
    public function show(Bitacora $bitacora): BitacoraResource
    {
        return (new BitacoraResource($bitacora))->additional([
            'success' => true,
            'message' => 'Bitacora retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given bitacora.
     *
     * Only the fields present in the validated payload are updated;
     * omitted fields retain their current values.
     *
     * @param  UpdateBitacoraRequest  $request  Validated update payload.
     * @param  Bitacora  $bitacora  The resolved Bitacora model instance.
     * @return BitacoraResource 200 with the updated resource envelope.
     */
    public function update(UpdateBitacoraRequest $request, Bitacora $bitacora): BitacoraResource
    {
        $bitacora->update($request->validated());

        return (new BitacoraResource($bitacora))->additional([
            'success' => true,
            'message' => 'Bitacora updated successfully.',
        ]);
    }

    /**
     * Soft-delete the bitacora record.
     *
     * The record is marked as deleted (sets `deleted_at`). It can be recovered
     * with {@see restore()}. Physical deletion never occurs through this endpoint.
     *
     * @param  Bitacora  $bitacora  The resolved Bitacora model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Bitacora $bitacora): JsonResponse
    {
        $bitacora->delete();

        return ApiResponse::success(null, 'Bitacora deleted successfully.');
    }

    /**
     * Restore a previously soft-deleted bitacora.
     *
     * Clears `deleted_at` so the record becomes visible again on the standard
     * index endpoint. The route for this action uses `{bitacora}` resolved
     * via `withTrashed` binding registered in `AppServiceProvider`.
     *
     * @param  Bitacora  $bitacora  The soft-deleted Bitacora model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Bitacora $bitacora): JsonResponse
    {
        $bitacora->restore();

        return ApiResponse::success(null, 'Bitacora restored successfully.');
    }
}
