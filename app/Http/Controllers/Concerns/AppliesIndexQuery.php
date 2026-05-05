<?php

namespace App\Http\Controllers\Concerns;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Provides the `applyIndexQuery` helper to controllers that need
 * filterable, sortable, paginated resource listings.
 *
 * Controllers that use this trait delegate the full index query pipeline
 * to {@see IndexQueryApplier}, keeping controller methods thin and
 * configuration-driven via {@see *IndexDefinition} classes.
 */
trait AppliesIndexQuery
{
    /**
     * Apply pagination, sorting, filtering, search, and eager-loading
     * to the given query builder using the supplied definition config.
     *
     * Delegates to {@see IndexQueryApplier::apply()}, which processes:
     * `page`, `per_page`, `sort`, `direction`, `search`, `include`, `fields`,
     * `only_trashed`, and `filter[*]` query parameters.
     *
     * @param  Builder                $query      The base Eloquent query builder.
     * @param  array<string, mixed>   $validated  Validated request parameters from the form request.
     * @param  array<string, mixed>   $config     Index definition returned by `{Model}IndexDefinition::definition()`.
     * @return LengthAwarePaginator<\Illuminate\Database\Eloquent\Model>  Paginated result set.
     */
    protected function applyIndexQuery(Builder $query, array $validated, array $config = []): LengthAwarePaginator
    {
        return app(IndexQueryApplier::class)->apply($query, $validated, $config);
    }
}
