<?php

namespace App\Http\Requests\Api\V1\Bien;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\BienIndexDefinition;

class IndexBienRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return BienIndexDefinition::definition();
    }
}
