<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Adjunto\IndexAdjuntoRequest;
use App\Http\Requests\Api\V1\Adjunto\StoreAdjuntoRequest;
use App\Http\Requests\Api\V1\Adjunto\UpdateAdjuntoRequest;
use App\Http\Resources\Api\V1\AdjuntoResource;
use App\Models\Adjunto;
use App\Models\Bien;
use App\Models\Informe;
use App\Models\Persona;
use App\Support\Http\ApiResponse;
use App\Support\Query\Definitions\AdjuntoIndexDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Handles CRUD, file delivery, and restore operations for Adjunto records.
 *
 * An adjunto is a polymorphic file attachment that can belong to an
 * {@see Informe}, {@see Persona}, or {@see Bien}. Files are stored in the
 * `public` disk under `adjuntos/{tipo}/`. When an adjunto is force-deleted,
 * the physical file is removed from storage via a model boot hook.
 *
 * Provides two delivery endpoints:
 * - `download` — forces a file download (Content-Disposition: attachment).
 * - `stream`   — serves the file inline for browser preview (Content-Disposition: inline).
 */
class AdjuntoController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, filterable list of adjuntos.
     *
     * Delegates to {@see IndexQueryApplier} using {@see AdjuntoIndexDefinition},
     * supporting search, sort, per-field filters (by `descripcion`, `informe_id`,
     * `persona_id`, `bien_id`), includes, field selection, pagination,
     * and an `only_trashed` soft-delete scope.
     *
     * @param  IndexAdjuntoRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<\Illuminate\Contracts\Pagination\LengthAwarePaginator<AdjuntoResource>>
     */
    public function index(IndexAdjuntoRequest $request): AnonymousResourceCollection
    {
        $adjuntos = $this->applyIndexQuery(
            Adjunto::query(),
            $request->validated(),
            AdjuntoIndexDefinition::definition(),
        );

        return AdjuntoResource::collection($adjuntos)->additional([
            'success' => true,
            'message' => 'Adjuntos retrieved successfully.',
        ]);
    }

    /**
     * Upload one or more files and attach them to the specified polymorphic owner.
     *
     * The `tipo` field determines which model class is the owner (`informe`,
     * `persona`, or `bien`). Each file is stored under `adjuntos/{tipo}/` on
     * the `public` disk; the original filename is used as the description.
     * All uploads within a single request share the same owner record.
     *
     * @param  StoreAdjuntoRequest  $request  Validated upload payload including `tipo`, `modelo_id`, and `archivos[]`.
     * @return JsonResponse  201 with a collection of {@see AdjuntoResource} envelopes.
     */
    public function store(StoreAdjuntoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $adjuntable = $this->resolveAdjuntable($validated['tipo'], $validated['modelo_id']);

        $adjuntos = collect($request->file('archivos'))
            ->map(function ($archivo) use ($adjuntable, $validated): Adjunto {
                $ruta = $archivo->store('adjuntos/' . $validated['tipo'], 'public');

                return $adjuntable->adjuntos()->create([
                    'url' => $ruta,
                    'descripcion' => $archivo->getClientOriginalName(),
                ]);
            });

        return AdjuntoResource::collection($adjuntos)
            ->additional([
                'success' => true,
                'message' => 'Adjuntos created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Return a single adjunto by its primary key.
     *
     * The response includes pre-built `download_url` and `stream_url` fields
     * resolved via named routes `v1.adjuntos.download` and `v1.adjuntos.stream`.
     *
     * @param  Adjunto  $adjunto  The resolved Adjunto model instance.
     * @return AdjuntoResource  200 with the resource envelope.
     */
    public function show(Adjunto $adjunto): AdjuntoResource
    {
        return (new AdjuntoResource($adjunto))->additional([
            'success' => true,
            'message' => 'Adjunto retrieved successfully.',
        ]);
    }

    /**
     * Force a file download with Content-Disposition: attachment.
     *
     * Checks that the file exists on the `public` disk before streaming;
     * aborts with 404 if the physical file is missing. The filename sent
     * to the browser is the adjunto's `descripcion`, falling back to the
     * basename of the stored path.
     *
     * @param  Adjunto  $adjunto  The resolved Adjunto model instance.
     * @return StreamedResponse  Binary file stream with attachment headers.
     */
    public function download(Adjunto $adjunto): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($adjunto->url), 404);

        return Storage::disk('public')->download(
            $adjunto->url,
            $adjunto->descripcion ?: basename($adjunto->url),
        );
    }

    /**
     * Serve the file inline for in-browser preview (Content-Disposition: inline).
     *
     * Identical to {@see download()} except the browser is instructed to
     * render the file inline (e.g. PDF viewer, image preview) rather than
     * saving it. Aborts with 404 if the physical file is missing from disk.
     *
     * @param  Adjunto  $adjunto  The resolved Adjunto model instance.
     * @return StreamedResponse  Binary file stream with inline content-disposition headers.
     */
    public function stream(Adjunto $adjunto): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($adjunto->url), 404);

        return Storage::disk('public')->response(
            $adjunto->url,
            $adjunto->descripcion ?: basename($adjunto->url),
        );
    }

    /**
     * Update the metadata (description and/or URL) of an existing adjunto.
     *
     * Does not replace the physical file on disk — only the database record
     * is modified. To replace the file, delete this adjunto and create a new one.
     *
     * @param  UpdateAdjuntoRequest  $request   Validated update payload.
     * @param  Adjunto               $adjunto   The resolved Adjunto model instance.
     * @return AdjuntoResource  200 with the updated resource envelope.
     */
    public function update(UpdateAdjuntoRequest $request, Adjunto $adjunto): AdjuntoResource
    {
        $adjunto->update($request->validated());

        return (new AdjuntoResource($adjunto))->additional([
            'success' => true,
            'message' => 'Adjunto updated successfully.',
        ]);
    }

    /**
     * Soft-delete the adjunto record (physical file is retained for restore).
     *
     * Only sets `deleted_at`; the file on disk is NOT removed. If the adjunto
     * is force-deleted (e.g. via cascade), the model boot hook in
     * {@see Adjunto::booted()} deletes the physical file from the `public` disk.
     *
     * @param  Adjunto  $adjunto  The resolved Adjunto model instance.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function destroy(Adjunto $adjunto): JsonResponse
    {
        $adjunto->delete();

        return ApiResponse::success(null, 'Adjunto deleted successfully.');
    }

    /**
     * Restore a soft-deleted adjunto.
     *
     * Clears `deleted_at` so the record is active again. The physical file
     * must still exist on disk for delivery endpoints to work after restore.
     *
     * @param  Adjunto  $adjunto  The soft-deleted Adjunto model instance to restore.
     * @return JsonResponse  200 with a success envelope and null data.
     */
    public function restore(Adjunto $adjunto): JsonResponse
    {
        $adjunto->restore();

        return ApiResponse::success(null, 'Adjunto restored successfully.');
    }

    /**
     * Resolve the polymorphic owner model from the `tipo` and `modelo_id` request values.
     *
     * Maps the string `tipo` to its corresponding Eloquent model class and performs
     * a `findOrFail` lookup. Throws a {@see \Illuminate\Database\Eloquent\ModelNotFoundException}
     * (→ 404) if the record does not exist, and aborts with 422 if `tipo` is not
     * one of the three allowed values (`informe`, `persona`, `bien`).
     *
     * @param  string  $tipo      One of: 'informe', 'persona', 'bien'.
     * @param  int     $modeloId  Primary key of the owner record.
     * @return Model  The resolved Eloquent model instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  If the owner record does not exist.
     */
    private function resolveAdjuntable(string $tipo, int $modeloId): Model
    {
        return match ($tipo) {
            'persona' => Persona::query()->findOrFail($modeloId),
            'bien' => Bien::query()->findOrFail($modeloId),
            'informe' => Informe::query()->findOrFail($modeloId),
        };
    }
}
