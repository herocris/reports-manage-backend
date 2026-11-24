<?php

namespace App\Http\Requests\Api\V1\Producto;

use App\Http\Requests\Api\V1\IndexRequest;
use App\Support\Query\Definitions\ProductoIndexDefinition;

class IndexProductoRequest extends IndexRequest
{
    /** @return array<string, mixed> */
    protected function definition(): array
    {
        return ProductoIndexDefinition::definition();
    }
}
