<?php

namespace App\Http\Requests\Api\V1\Ambito;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\AmbitoIndexDefinition;

class IndexAmbitoRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return AmbitoIndexDefinition::definition();
    }
}
