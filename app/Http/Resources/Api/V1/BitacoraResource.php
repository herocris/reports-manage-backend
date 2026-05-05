<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Bitacora model into the API JSON shape.
 *
 * Conditionally includes the `informe` relation when it has been
 * eager-loaded by the calling controller.
 */
class BitacoraResource extends JsonResource
{
    /**
     * Transform the Bitacora model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed>  Shape: {id, descripcion?, fecha?, informe_id?,
     *   created_at?, updated_at?, informe?: InformeResource}
     *   Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'descripcion' => $this->whenHas('descripcion', fn () => (string) $this->descripcion),
            'fecha' => $this->whenHas('fecha', fn () => $this->fecha),
            'informe_id' => $this->whenHas('informe_id', fn () => (int) $this->informe_id),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
            'informe' => new InformeResource($this->whenLoaded('informe')),
        ];
    }
}
