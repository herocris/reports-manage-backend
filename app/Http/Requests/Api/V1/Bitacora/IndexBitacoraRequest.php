<?php

namespace App\Http\Requests\Api\V1\Bitacora;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\BitacoraIndexDefinition;

class IndexBitacoraRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return BitacoraIndexDefinition::definition();
    }
}
