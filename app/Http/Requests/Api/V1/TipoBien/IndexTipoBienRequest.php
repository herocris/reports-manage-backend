<?php

namespace App\Http\Requests\Api\V1\TipoBien;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\TipoBienIndexDefinition;

class IndexTipoBienRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return TipoBienIndexDefinition::definition();
    }
}
