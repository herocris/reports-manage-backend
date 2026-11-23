<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObservacionComentarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'observacion_id' => (int) $this->observacion_id,
            'usuario_id' => (int) $this->usuario_id,
            'contenido' => (string) $this->contenido,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'usuario' => new UserResource($this->whenLoaded('usuario')),
        ];
    }
}
