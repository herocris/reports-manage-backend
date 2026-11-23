<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an Observacion model into the API JSON shape.
 *
 * Conditionally includes the `informe` relation when eager-loaded.
 */
class ObservacionResource extends JsonResource
{
    /**
     * Transform the Observacion model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed> Shape: {id, descripcion?, usuario_id?, informe_id?,
     *                              created_at?, updated_at?, informe?: InformeResource}
     *                              Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'numero' => $this->whenHas('numero', fn () => (int) $this->numero),
            'titulo' => $this->whenHas('titulo', fn () => (string) $this->titulo),
            'descripcion' => $this->whenHas('descripcion', fn () => (string) $this->descripcion),
            'estado' => $this->whenHas('estado', fn () => $this->estado->value),
            'usuario_id' => $this->whenHas('usuario_id', fn () => (int) $this->usuario_id),
            'informe_id' => $this->whenHas('informe_id', fn () => (int) $this->informe_id),
            'cerrada_at' => $this->whenHas('cerrada_at', fn () => $this->cerrada_at),
            'cerrada_por_id' => $this->whenHas('cerrada_por_id', fn () => $this->cerrada_por_id ? (int) $this->cerrada_por_id : null),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
            'informe' => new InformeResource($this->whenLoaded('informe')),
            'usuario' => new UserResource($this->whenLoaded('usuario')),
            'cerrada_por' => new UserResource($this->whenLoaded('cerradaPor')),
            'comentarios' => ObservacionComentarioResource::collection($this->whenLoaded('comentarios')),
        ];
    }
}
