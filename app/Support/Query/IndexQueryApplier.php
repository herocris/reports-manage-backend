<?php

namespace App\Support\Query;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Applies the full index pipeline—pagination, sorting, search, filters,
 * field selection, includes, and soft-delete scoping—to an Eloquent query.
 *
 * Consumed by controllers via the {@see \App\Http\Controllers\Concerns\AppliesIndexQuery} trait.
 */
class IndexQueryApplier
{
    /**
     * Run the complete index pipeline and return a paginated result.
     *
     * @param  Builder  $query      The base Eloquent query builder.
     * @param  array<string, mixed>  $validated  Validated query-string parameters.
     * @param  array<string, mixed>  $config     Definition array from an `*IndexDefinition::definition()` call.
     */
    public function apply(Builder $query, array $validated, array $config = []): LengthAwarePaginator
    {
        $this->applyOnlyTrashed($query, (bool) ($validated['only_trashed'] ?? false));
        $this->applyIncludes($query, $validated['include'] ?? null, $config['includes'] ?? []);
        $this->applyFields($query, $validated['fields'] ?? null, $config['fields'] ?? []);
        $this->applySearch($query, (string) ($validated['search'] ?? ''), $config['searchable'] ?? []);
        $this->applyFilters($query, $validated['filter'] ?? [], $config['filters'] ?? []);

        $sort = $this->resolveSort(
            $validated['sort'] ?? null,
            $config['sortable'] ?? [],
            (string) ($config['default_sort'] ?? 'created_at'),
        );

        $direction = (string) ($validated['direction'] ?? ($config['default_direction'] ?? 'desc'));
        $perPage = (int) ($validated['per_page'] ?? ($config['default_per_page'] ?? 15));

        return $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Scope the query to soft-deleted records only when `only_trashed` is requested.
     */
    private function applyOnlyTrashed(Builder $query, bool $onlyTrashed): void
    {
        if ($onlyTrashed && $this->usesSoftDeletes($query)) {
            $query->onlyTrashed();
        }
    }

    /**
     * Eager-load relationships whitelisted in the definition's `includes` array.
     *
     * @param  string[]  $includeMap  Allowed relationship names.
     */
    private function applyIncludes(Builder $query, mixed $rawIncludes, array $includeMap): void
    {
        $requestedIncludes = is_array($rawIncludes) ? $rawIncludes : [];

        if (empty($requestedIncludes) || empty($includeMap)) {
            return;
        }

        $includes = array_values(array_intersect($requestedIncludes, $includeMap));

        if (! empty($includes)) {
            $query->with($includes);
        }
    }

    /**
     * Restrict the SELECT clause to whitelisted columns, always keeping the primary key.
     *
     * @param  string[]  $selectableFields  Columns allowed by the definition.
     */
    private function applyFields(Builder $query, mixed $rawFields, array $selectableFields): void
    {
        $requestedFields = is_array($rawFields) ? $rawFields : [];

        if (empty($requestedFields) || empty($selectableFields)) {
            return;
        }

        $fields = array_values(array_intersect($requestedFields, $selectableFields));
        $keyName = $query->getModel()->getKeyName();

        if (! in_array($keyName, $fields, true)) {
            $fields[] = $keyName;
        }

        $query->select($fields);
    }

    /**
     * Add a LIKE-based full-text search across all searchable columns.
     *
     * @param  string[]  $searchable  Columns to search against.
     */
    private function applySearch(Builder $query, string $searchTerm, array $searchable): void
    {
        $term = trim($searchTerm);

        if ($term === '' || empty($searchable)) {
            return;
        }

        $query->where(function (Builder $builder) use ($searchable, $term): void {
            foreach ($searchable as $index => $field) {
                if ($index === 0) {
                    $builder->where($field, 'like', '%'.$term.'%');

                    continue;
                }

                $builder->orWhere($field, 'like', '%'.$term.'%');
            }
        });
    }

    /**
     * Apply per-field filters from the request against the definition's filter map.
     *
     * Each entry in `$filterMap` is either a column name string (simple equality)
     * or a `Closure(Builder, mixed): void` for complex queries.
     *
     * @param  array<string, string|Closure>  $filterMap  Allowed filter definitions.
     */
    private function applyFilters(Builder $query, mixed $rawFilters, array $filterMap): void
    {
        $filters = is_array($rawFilters) ? $rawFilters : [];

        if (empty($filters) || empty($filterMap)) {
            return;
        }

        foreach ($filters as $key => $value) {
            if ($value === null || $value === '' || ! array_key_exists($key, $filterMap)) {
                continue;
            }

            $handler = $filterMap[$key];

            if (is_string($handler)) {
                $query->where($handler, $value);

                continue;
            }

            if ($handler instanceof Closure) {
                $handler($query, $value);
            }
        }
    }

    /**
     * Resolve the sort column, falling back to the default if the requested
     * column is not in the sortable whitelist.
     *
     * @param  string[]  $sortable     Allowed sort columns.
     * @param  string    $defaultSort  Fallback column name.
     */
    private function resolveSort(mixed $rawSort, array $sortable, string $defaultSort): string
    {
        $sort = is_string($rawSort) ? $rawSort : $defaultSort;

        if (! empty($sortable) && ! in_array($sort, $sortable, true)) {
            return $defaultSort;
        }

        return $sort;
    }

    /**
     * Determine whether the query's model uses the SoftDeletes trait.
     */
    private function usesSoftDeletes(Builder $query): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($query->getModel()), true);
    }
}
