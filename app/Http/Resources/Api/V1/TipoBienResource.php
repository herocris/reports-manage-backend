<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a TipoBien model into the API JSON shape.
 *
 * Fields use `whenHas()` to support field selection via the `fields` query parameter.
 */
class TipoBienResource extends JsonResource
{
    /**
     * Transform the TipoBien model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed>  Shape: {id, nombre?, descripcion?, created_at?, updated_at?}
     *   Fields marked with `?` are omitted when not loaded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'nombre' => $this->whenHas('nombre', fn () => (string) $this->nombre),
            'descripcion' => $this->whenHas('descripcion', fn () => (string) $this->descripcion),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at),
            'updated_at' => $this->whenHas('updated_at', fn () => $this->updated_at),
        ];
    }
}
