<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Persona model into the API JSON shape.
 *
 * Conditionally includes `adjuntos` (polymorphic file attachments)
 * when the relation has been eager-loaded.
 */
class PersonaResource extends JsonResource
{
    /**
     * Transform the Persona model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed>  Shape: {id, nombre?, numero_identificacion?,
     *   created_at?, updated_at?, adjuntos?: AdjuntoResource[]}
     *   Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'nombre' => $this->whenHas('nombre', fn () => (string) $this->nombre),
            'numero_identificacion' => $this->whenHas('numero_identificacion', fn () => (string) $this->numero_identificacion),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
            'adjuntos' => AdjuntoResource::collection($this->whenLoaded('adjuntos')),
        ];
    }
}
