<?php

namespace App\Support\Query;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Applies the full index pipeline—pagination, sorting, search, filters,
 * field selection, includes, and soft-delete scoping—to an Eloquent query.
 */
class IndexQueryApplier
{
    /**
     * Run the complete index pipeline and return a paginated result.
     *
     * @param  Builder  $query  The base Eloquent query builder.
     * @param  array<string, mixed>  $validated  Validated query-string parameters.
     * @param  array<string, mixed>  $config  Definition array from an `*IndexDefinition::definition()` call.
     */
    public function apply(Builder $query, array $validated, array $config = []): LengthAwarePaginator
    {
        $this->applyOnlyTrashed($query, (bool) ($validated['only_trashed'] ?? false));
        $this->applyIncludes($query, $validated['include'] ?? null, $config['includes'] ?? [], $config['include_loads'] ?? []);
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

        $query->orderBy($sort, $direction);

        if ($sort !== $query->getModel()->getKeyName()) {
            $query->orderBy($query->getModel()->getQualifiedKeyName(), $direction);
        }

        return $query
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
     * @param  array<string, string[]>  $includeLoads  Additional relationships required to serialize an include.
     */
    private function applyIncludes(Builder $query, mixed $rawIncludes, array $includeMap, array $includeLoads): void
    {
        $requestedIncludes = is_array($rawIncludes) ? $rawIncludes : [];

        if (empty($requestedIncludes) || empty($includeMap)) {
            return;
        }

        $includes = array_values(array_intersect($requestedIncludes, $includeMap));

        if (! empty($includes)) {
            foreach ($includes as $include) {
                $includes = array_merge($includes, $includeLoads[$include] ?? []);
            }

            $query->with(array_unique($includes));
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

        foreach (array_keys($query->getEagerLoads()) as $include) {
            $relationName = explode('.', $include)[0];
            $relation = $query->getModel()->{$relationName}();

            if ($relation instanceof BelongsTo) {
                $fields[] = $relation->getForeignKeyName();
            }

            if ($relation instanceof MorphTo) {
                $fields[] = $relation->getMorphType();
            }

            if ($relation instanceof HasOneOrMany) {
                $fields[] = $relation->getLocalKeyName();
            }

            if ($relation instanceof BelongsToMany) {
                $fields[] = $relation->getParentKeyName();
            }
        }

        $query->getQuery()->columns = array_values(array_filter(
            $query->getQuery()->columns ?? [],
            fn (mixed $column): bool => ! is_string($column) || str_contains(strtolower($column), ' as '),
        ));

        $query->addSelect(array_unique($fields));
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
     * Each filter contains validation rules and either a column name (equality)
     * or a closure that applies a complex query.
     *
     * @param  array<string, array{rules: array<int, mixed>, query: string|Closure}>  $filterMap
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

            $handler = $filterMap[$key]['query'];

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
     * @param  string[]  $sortable  Allowed sort columns.
     * @param  string  $defaultSort  Fallback column name.
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
