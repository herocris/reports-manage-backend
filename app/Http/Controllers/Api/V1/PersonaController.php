<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Persona\IndexPersonaRequest;
use App\Http\Requests\Api\V1\Persona\StorePersonaRequest;
use App\Http\Requests\Api\V1\Persona\UpdatePersonaRequest;
use App\Http\Resources\Api\V1\PersonaResource;
use App\Models\Informe;
use App\Models\Persona;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\PersonaIndexDefinition;
use App\Support\Query\IndexQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Handles CRUD operations for Persona records.
 *
 * A Persona is a natural person (individual) associated with one or more Informes
 * via the `informe_persona` pivot table. When created, the `informe_id` from the
 * request is used to attach the new persona to the informe. The `informe_id` is
 * not stored on the persona record itself.
 */
class PersonaController extends Controller
{
    /**
     * Return a paginated, filterable list of personas.
     *
     * Applies the full index pipeline via {@see IndexQueryApplier} using
     * {@see PersonaIndexDefinition}. Supports search, sort, per-field filters,
     * includes, field selection, pagination, and `only_trashed` soft-delete scope.
     *
     * @param  IndexPersonaRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<PersonaResource>>
     */
    public function index(IndexPersonaRequest $request, IndexQueryApplier $indexQueryApplier): AnonymousResourceCollection
    {
        $personas = $indexQueryApplier->apply(
            Persona::query(),
            $request->validated(),
            PersonaIndexDefinition::definition(),
        );

        return PersonaResource::collection($personas)->additional([
            'success' => true,
            'message' => 'Personas retrieved successfully.',
        ]);
    }

    /**
     * Create a new persona and attach it to the specified informe.
     *
     * Resolves the `informe_id` via `findOrFail` (throws 404 if not found),
     * creates the persona record, then inserts a pivot row in `informe_persona`.
     * The `informe_id` field is stripped from the persona's own attributes.
     *
     * @param  StorePersonaRequest  $request  Validated creation payload.
     * @return JsonResponse 201 with the created {@see PersonaResource} envelope.
     */
    public function store(StorePersonaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $persona = DB::transaction(fn (): Persona => Informe::query()
            ->findOrFail($validated['informe_id'])
            ->personas()
            ->create(Arr::except($validated, ['informe_id'])));

        return (new PersonaResource($persona))
            ->additional([
                'success' => true,
                'message' => 'Persona created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single persona by its primary key.
     *
     * @param  Persona  $persona  The resolved Persona model instance.
     * @return PersonaResource 200 with the resource envelope.
     */
    public function show(Persona $persona): PersonaResource
    {
        return (new PersonaResource($persona))->additional([
            'success' => true,
            'message' => 'Persona retrieved successfully.',
        ]);
    }

    /**
     * Return the persona with its full association tree eager-loaded for an edit form.
     *
     * @param  Persona  $persona  The resolved Persona model instance.
     * @return PersonaResource 200 with the resource envelope.
     */
    public function edit(Persona $persona): PersonaResource
    {
        return (new PersonaResource($persona->load('adjuntos')))->additional([
            'success' => true,
            'message' => 'Persona edit data retrieved successfully.',
        ]);
    }

    /**
     * Apply a partial or full update to the given persona.
     *
     * @param  UpdatePersonaRequest  $request  Validated update payload.
     * @param  Persona  $persona  The resolved Persona model instance.
     * @return PersonaResource 200 with the updated resource envelope.
     */
    public function update(UpdatePersonaRequest $request, Persona $persona): PersonaResource
    {
        $persona->update($request->validated());

        return (new PersonaResource($persona))->additional([
            'success' => true,
            'message' => 'Persona updated successfully.',
        ]);
    }

    /**
     * Soft-delete the given persona.
     *
     * Sets `deleted_at`; pivot rows in `informe_persona` are NOT removed.
     * The persona can be recovered via {@see restore()}.
     *
     * @param  Persona  $persona  The resolved Persona model instance.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function destroy(Persona $persona): JsonResponse
    {
        $persona->delete();

        return ApiResponse::success(null, 'Persona deleted successfully.');
    }

    /**
     * Restore a soft-deleted persona.
     *
     * Clears `deleted_at`; the route binding uses a `withTrashed` scope
     * registered in `AppServiceProvider` so the trashed record resolves correctly.
     *
     * @param  Persona  $persona  The soft-deleted Persona model instance to restore.
     * @return JsonResponse 200 with a success envelope and null data.
     */
    public function restore(Persona $persona): JsonResponse
    {
        $persona->restore();

        return ApiResponse::success(null, 'Persona restored successfully.');
    }
}
