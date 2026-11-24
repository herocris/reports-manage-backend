<?php

namespace App\Http\Requests\Api\V1\Adjunto;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\AdjuntoIndexDefinition;

class IndexAdjuntoRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return AdjuntoIndexDefinition::definition();
    }
}
