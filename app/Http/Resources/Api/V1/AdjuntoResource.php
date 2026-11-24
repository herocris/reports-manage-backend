<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an Adjunto model into the API JSON shape.
 *
 * Exposes computed `download_url` and `stream_url` fields pointing
 * to the inline-preview and forced-download endpoints.
 */
class AdjuntoResource extends JsonResource
{
    /**
     * Transform the Adjunto model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed> Shape: {id, url, descripcion,
     *                              adjuntable_id, adjuntable_type, download_url (route v1.adjuntos.download),
     *                              stream_url (route v1.adjuntos.stream), created_at, updated_at}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'url' => (string) $this->url,
            'descripcion' => (string) $this->descripcion,
            'adjuntable_id' => (int) $this->adjuntable_id,
            'adjuntable_type' => (string) $this->adjuntable_type,
            'download_url' => route('v1.adjuntos.download', $this->id),
            'stream_url' => route('v1.adjuntos.stream', $this->id),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
