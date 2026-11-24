<?php

namespace App\Http\Requests\Api\V1\Observacion;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\ObservacionIndexDefinition;

class IndexObservacionRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return ObservacionIndexDefinition::definition();
    }
}
