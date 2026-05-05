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
     * @return array<string, mixed>  Shape: {id, descripcion?, usuario_id?, informe_id?,
     *   created_at?, updated_at?, informe?: InformeResource}
     *   Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'descripcion' => $this->whenHas('descripcion', fn () => (string) $this->descripcion),
            'usuario_id' => $this->whenHas('usuario_id', fn () => (int) $this->usuario_id),
            'informe_id' => $this->whenHas('informe_id', fn () => (int) $this->informe_id),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
            'informe' => new InformeResource($this->whenLoaded('informe')),
        ];
    }
}
