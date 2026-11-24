<?php

namespace App\Http\Requests\Api\V1\Persona;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\PersonaIndexDefinition;

class IndexPersonaRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return PersonaIndexDefinition::definition();
    }
}
