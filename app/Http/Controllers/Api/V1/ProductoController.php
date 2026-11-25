<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Producto\IndexProductoRequest;
use App\Http\Requests\Api\V1\Producto\StoreProductoRequest;
use App\Http\Requests\Api\V1\Producto\UpdateProductoRequest;
use App\Http\Resources\Api\V1\ProductoResource;
use App\Models\Producto;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\ProductoIndexDefinition;
use App\Support\Query\IndexQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles CRUD operations for Producto records.
 *
 * A Producto is a audit product or work output category that groups informes.
 * It supports soft-delete; deleted products can be listed via `only_trashed`
 * and restored without data loss. Active informes referencing a deleted
 * producto will continue to hold the FK.
 */
class ProductoController extends Controller
{
    /**
     * Return a paginated, filterable list of productos.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see ProductoIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexProductoRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<ProductoResource>>
     */
    public function index(IndexProductoRequest $request, IndexQueryApplier $indexQueryApplier): AnonymousResourceCollection
    {
        $productos = $indexQueryApplier->apply(
            Producto::query(),
            $request->validated(),
            ProductoIndexDefinition::definition(),
        );

        return ProductoResource::collection($productos)->additional([
            'success' => true,
            'message' => 'Productos retrieved successfully.',
        ]);
    }

    /**
     * Persist a new producto record.
     *
     * @param  StoreProductoRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see ProductoResource} envelope.
     */
    public function store(StoreProductoRequest $request): JsonResponse
    {
        $producto = Producto::create($request->validated());

        return (new ProductoResource($producto))
            ->additional([
                'success' => true,
                'message' => 'Producto created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single producto by its primary key.
     *
     * @param  Producto  $producto  The resolved Producto model instance.
     * @return ProductoResource 200 with the resource envelope.
     */
    public function show(Producto $producto): ProductoResource
    {
        return (new ProductoResource($producto))->additional([
            'success' => true,
            'message' => 'Producto retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given producto.
     *
     * @param  UpdateProductoRequest  $request  Validated update payload.
     * @param  Producto  $producto  The resolved Producto model instance.
     * @return ProductoResource 200 with the updated resource envelope.
     */
    public function update(UpdateProductoRequest $request, Producto $producto): ProductoResource
    {
        $producto->update($request->validated());

        return (new ProductoResource($producto))->additional([
            'success' => true,
            'message' => 'Producto updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given producto.
     *
     * Sets `deleted_at`; the record can be recovered via {@see restore()}.
     *
     * @param  Producto  $producto  The resolved Producto model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        return ApiResponse::success(null, 'Producto deleted successfully.');
    }

    /**
     * Restore a soft-deleted producto.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Producto  $producto  The soft-deleted Producto model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Producto $producto): JsonResponse
    {
        $producto->restore();

        return ApiResponse::success(null, 'Producto restored successfully.');
    }
}
