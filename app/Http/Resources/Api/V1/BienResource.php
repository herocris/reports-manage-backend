<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Bien model into the API JSON shape.
 *
 * Conditionally includes `tipoBien` and `adjuntos` relations when
 * they have been eager-loaded by the calling controller.
 */
class BienResource extends JsonResource
{
    /**
     * Transform the Bien model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed>  Shape: {id, descripcion?, tipobien_id?, created_at?,
     *   updated_at?, tipoBien?: TipoBienResource, adjuntos?: AdjuntoResource[]}
     *   Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'descripcion' => $this->whenHas('descripcion', fn () => (string) $this->descripcion),
            'tipobien_id' => $this->whenHas('tipobien_id', fn () => (int) $this->tipobien_id),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
            'tipoBien' => new TipoBienResource($this->whenLoaded('tipoBien')),
            'adjuntos' => AdjuntoResource::collection($this->whenLoaded('adjuntos')),
        ];
    }
}
