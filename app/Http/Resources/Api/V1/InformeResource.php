<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an Informe model into the API JSON shape.
 *
 * All relational keys (producto, personas, bienes, adjuntos, ubicaciones,
 * observaciones, bitacoras, ambitos) are conditional on eager-loading,
 * so they are omitted unless explicitly included via the `include` query parameter.
 */
class InformeResource extends JsonResource
{
    /**
     * Transform the Informe model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed> Shape: {id, expediente?, titulo?, producto_id?, fecha_inicio?,
     *                              fecha_final?, creador?: UserResource, created_at?, updated_at?,
     *                              producto?: ProductoResource, personas?: PersonaResource[],
     *                              bienes?: BienResource[], adjuntos?: AdjuntoResource[],
     *                              ubicaciones?: UbicacionResource[], observaciones?: ObservacionResource[],
     *                              bitacoras?: BitacoraResource[], ambitos?: AmbitoResource[]}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'expediente' => $this->whenHas('expediente', fn () => (string) $this->expediente),
            'titulo' => $this->whenHas('titulo', fn () => (string) $this->titulo),
            'producto_id' => $this->whenHas('producto_id', fn () => (int) $this->producto_id),
            'fecha_inicio' => $this->whenHas('fecha_inicio', fn () => $this->fecha_inicio),
            'fecha_final' => $this->whenHas('fecha_final', fn () => $this->fecha_final),
            'creador' => new UserResource($this->whenLoaded('usuarioCreador')),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
            'producto' => new ProductoResource($this->whenLoaded('producto')),
            'personas' => PersonaResource::collection($this->whenLoaded('personas')),
            'bienes' => BienResource::collection($this->whenLoaded('bienes')),
            'adjuntos' => AdjuntoResource::collection($this->whenLoaded('adjuntos')),
            'ubicaciones' => UbicacionResource::collection($this->whenLoaded('ubicaciones')),
            'observaciones' => ObservacionResource::collection($this->whenLoaded('observaciones')),
            'bitacoras' => BitacoraResource::collection($this->whenLoaded('bitacoras')),
            'ambitos' => AmbitoResource::collection($this->whenLoaded('ambitos')),
        ];
    }
}
