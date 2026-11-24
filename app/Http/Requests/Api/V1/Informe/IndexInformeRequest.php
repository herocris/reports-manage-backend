<?php

namespace App\Http\Requests\Api\V1\Informe;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\InformeIndexDefinition;

class IndexInformeRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return InformeIndexDefinition::definition();
    }
}
