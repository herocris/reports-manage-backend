<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Informe\IndexInformeRequest;
use App\Http\Requests\Api\V1\Informe\StoreInformeRequest;
use App\Http\Requests\Api\V1\Informe\UpdateInformeRequest;
use App\Http\Resources\Api\V1\InformeResource;
use App\Models\Bien;
use App\Models\Informe;
use App\Models\Persona;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\InformeIndexDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

/**
 * Handles CRUD, cascade delete/restore, and relationship operations for Informe records.
 *
 * An Informe is the central entity of the application, representing a formal report.
 * It owns several child collections (ubicaciones, observaciones, bitacoras, adjuntos)
 * which are cascade-deleted and restored together with the parent. It also has
 * many-to-many relationships with Personas and Bienes managed via dedicated endpoints.
 *
 * The `edit` action eager-loads the full association tree to reduce round-trips
 * when loading a form in a client application.
 */
class InformeController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of informes.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see InformeIndexDefinition}. Supports search across `expediente` and
     * `titulo`, per-field filters, eager-loading of relations, field selection,
     * configurable pagination, sort direction, and `only_trashed` scope.
     *
     * @param  IndexInformeRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<\Illuminate\Contracts\Pagination\LengthAwarePaginator<InformeResource>>
     */
    public function index(IndexInformeRequest $request): AnonymousResourceCollection
    {
        $informes = $this->applyIndexQuery(
            Informe::query(),
            $request->validated(),
            InformeIndexDefinition::definition(),
        );

        return InformeResource::collection($informes)->additional([
            'success' => true,
            'message' => 'Informes retrieved successfully.',
        ]);
    }

    /**
     * Persist a new informe and optionally attach ambitos.
     *
     * The `usuariocreador_id` is automatically set from the authenticated user;
     * it is never accepted from the request payload. If `ambitos` is present,
     * the pivot table `ambito_informe` is synced to the supplied list of IDs.
     *
     * @param  StoreInformeRequest  $request  Validated creation payload.
     * @return JsonResponse  201 with the created {@see InformeResource} envelope.
     */
    public function store(StoreInformeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['usuariocreador_id'] = (int) Auth::id();
        $informe = Informe::create($validated);

        if (isset($validated['ambitos'])) {
            $informe->ambitos()->sync($validated['ambitos']);
        }

        return (new InformeResource($informe))
            ->additional([
                'success' => true,
                'message' => 'Informe created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single informe by its primary key.
     *
     * Relations are NOT eagerly loaded here; to get the full association tree
     * with all child records, use the {@see edit()} endpoint instead.
     *
     * @param  Informe  $informe  The resolved Informe model instance.
     * @return InformeResource  200 with the resource envelope.
     */
    public function show(Informe $informe): InformeResource
    {
        return (new InformeResource($informe))->additional([
            'success' => true,
            'message' => 'Informe retrieved successfully.',
        ]);
    }

    /**
     * Return the informe with its full association tree eager-loaded for an edit form.
     *
     * Loads: `producto`, `personas`, `bienes`, `adjuntos`, `ubicaciones`,
     * `observaciones`, `bitacoras`, and `ambitos` in a single query batch.
     * Use this instead of {@see show()} when populating a form that needs
     * access to all related data.
     *
     * @param  Informe  $informe  The resolved Informe model instance.
     * @return InformeResource  200 with the resource envelope including all relations.
     */
    public function edit(Informe $informe): InformeResource
    {
        $informe->load([
            'producto',
            'personas',
            'bienes',
            'adjuntos',
            'ubicaciones',
            'observaciones',
            'bitacoras',
            'ambitos',
        ]);

        return (new InformeResource($informe))->additional([
            'success' => true,
            'message' => 'Informe edit data retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given informe.
     *
     * If `ambitos` is present in the validated payload, the pivot table is
     * fully re-synced to the supplied array of Ambito IDs (previous pivots not
     * in the new list are detached). Omitting `ambitos` leaves existing pivots unchanged.
     *
     * @param  UpdateInformeRequest  $request  Validated update payload.
     * @param  Informe               $informe  The resolved Informe model instance.
     * @return InformeResource  200 with the updated resource envelope.
     */
    public function update(UpdateInformeRequest $request, Informe $informe): InformeResource
    {
        $validated = $request->validated();
        $informe->update($validated);

        if (array_key_exists('ambitos', $validated)) {
            $informe->ambitos()->sync($validated['ambitos']);
        }

        return (new InformeResource($informe))->additional([
            'success' => true,
            'message' => 'Informe updated successfully.',
        ]);
    }

    /**
     * Soft-delete the informe and cascade to all owned child records.
     *
     * Sets `deleted_at` on the informe and on its `ubicaciones`, `observaciones`,
     * `bitacoras`, and `adjuntos`. Pivot rows for `personas` and `bienes` are NOT
     * removed (detach is not needed since those are independent entities).
     * Physical files attached via adjuntos are retained; they are only deleted
     * on force-delete.
     *
     * @param  Informe  $informe  The resolved Informe model instance.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function destroy(Informe $informe): JsonResponse
    {
        $informe->ubicaciones()->delete();
        $informe->observaciones()->delete();
        $informe->bitacoras()->delete();
        $informe->adjuntos()->delete();
        $informe->delete();

        return ApiResponse::success(null, 'Informe deleted successfully.');
    }

    /**
     * Restore the informe and all its cascade-deleted child records.
     *
     * Clears `deleted_at` on the informe and on each of its soft-deleted
     * `ubicaciones`, `observaciones`, `bitacoras`, and `adjuntos`.
     * The route for this action resolves the `{informe}` segment via a
     * `withTrashed` binding registered in `AppServiceProvider`.
     *
     * @param  Informe  $informe  The soft-deleted Informe model instance to restore.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function restore(Informe $informe): JsonResponse
    {
        $informe->restore();
        $informe->ubicaciones()->withTrashed()->restore();
        $informe->observaciones()->withTrashed()->restore();
        $informe->bitacoras()->withTrashed()->restore();
        $informe->adjuntos()->withTrashed()->restore();

        return ApiResponse::success(null, 'Informe restored successfully.');
    }

    /**
     * Link a Persona to the informe through the `informe_persona` pivot table.
     *
     * Validates that `persona_id` exists in the `personas` table, then
     * checks for an existing link before attaching to avoid duplicate pivots.
     * Returns 422 if the persona is already linked to this informe.
     *
     * @param  Request  $request  Inline-validated request containing `persona_id` (integer, required, exists:personas,id).
     * @param  Informe  $informe  The resolved Informe model instance.
     * @return JsonResponse  200 on success, 422 if the persona is already attached.
     */
    public function attachPersona(Request $request, Informe $informe): JsonResponse
    {
        $request->validate([
            'persona_id' => ['required', 'integer', 'exists:personas,id'],
        ]);

        if ($informe->personas()->where('persona_id', $request->integer('persona_id'))->exists()) {
            return ApiResponse::error('La persona ya está vinculada a este informe.', 422);
        }

        $informe->personas()->attach($request->integer('persona_id'));

        return ApiResponse::success(null, 'Persona vinculada al informe exitosamente.');
    }

    /**
     * Unlink a Persona from the informe's `informe_persona` pivot table.
     *
     * The Persona record itself is not affected; only the pivot row is removed.
     * Silently succeeds even if the persona is not currently linked.
     *
     * @param  Informe  $informe  The resolved Informe model instance.
     * @param  Persona  $persona  The resolved Persona model instance to detach.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function detachPersona(Informe $informe, Persona $persona): JsonResponse
    {
        $informe->personas()->detach($persona->id);

        return ApiResponse::success(null, 'Persona desvinculada del informe exitosamente.');
    }

    /**
     * Link a Bien to the informe through the `bien_informe` pivot table.
     *
     * Validates that `bien_id` exists in the `biens` table, then
     * checks for an existing link before attaching to avoid duplicate pivots.
     * Returns 422 if the bien is already linked to this informe.
     *
     * @param  Request  $request  Inline-validated request containing `bien_id` (integer, required, exists:biens,id).
     * @param  Informe  $informe  The resolved Informe model instance.
     * @return JsonResponse  200 on success, 422 if the bien is already attached.
     */
    public function attachBien(Request $request, Informe $informe): JsonResponse
    {
        $request->validate([
            'bien_id' => ['required', 'integer', 'exists:biens,id'],
        ]);

        if ($informe->bienes()->where('bien_id', $request->integer('bien_id'))->exists()) {
            return ApiResponse::error('El bien ya está vinculado a este informe.', 422);
        }

        $informe->bienes()->attach($request->integer('bien_id'));

        return ApiResponse::success(null, 'Bien vinculado al informe exitosamente.');
    }

    /**
     * Unlink a Bien from the informe's `bien_informe` pivot table.
     *
     * The Bien record itself is not affected; only the pivot row is removed.
     * Silently succeeds even if the bien is not currently linked.
     *
     * @param  Informe  $informe  The resolved Informe model instance.
     * @param  Bien     $bien     The resolved Bien model instance to detach.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function detachBien(Informe $informe, Bien $bien): JsonResponse
    {
        $informe->bienes()->detach($bien->id);

        return ApiResponse::success(null, 'Bien desvinculado del informe exitosamente.');
    }
}
