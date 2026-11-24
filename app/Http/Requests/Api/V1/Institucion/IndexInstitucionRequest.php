<?php

namespace App\Http\Requests\Api\V1\Institucion;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\InstitucionIndexDefinition;

class IndexInstitucionRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return InstitucionIndexDefinition::definition();
    }
}
