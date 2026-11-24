<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Producto model into the API JSON shape.
 *
 * Conditionally includes `informes` (a collection of InformeResource)
 * when the relation has been eager-loaded.
 */
class ProductoResource extends JsonResource
{
    /**
     * Transform the Producto model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed> Shape: {id, nombre?, descripcion?,
     *                              created_at?, updated_at?, informes?: InformeResource[]}
     *                              Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'nombre' => $this->whenHas('nombre', fn () => (string) $this->nombre),
            'descripcion' => $this->whenHas('descripcion', fn () => (string) $this->descripcion),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
            'informes' => InformeResource::collection($this->whenLoaded('informes')),
        ];
    }
}
