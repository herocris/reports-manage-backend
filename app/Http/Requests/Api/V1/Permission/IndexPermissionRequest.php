<?php

namespace App\Http\Requests\Api\V1\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for listing permissions.
 *
 * Supports pagination, sorting, search, filtering, includes, field selection, and only_trashed scope.
 */
class IndexPermissionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:permission.view` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the index query parameters.
     *
     * Accepted keys: `page`, `per_page` (1–100), `sort` (id|name|guard_name|created_at|updated_at),
     * `direction` (asc|desc), `search` (max 255), `include[]` (roles), `fields[]`,
     * `only_trashed` (bool), `filter[name]`, `filter[guard_name]`, `filter[role]`.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['id', 'name', 'guard_name', 'created_at', 'updated_at'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['sometimes', 'string', 'max:255'],
            'include' => ['sometimes', 'array'],
            'include.*' => ['string', Rule::in(['roles'])],
            'fields' => ['sometimes', 'array'],
            'fields.*' => ['string', Rule::in(['id', 'name', 'guard_name', 'created_at', 'updated_at'])],
            'only_trashed' => ['sometimes', 'boolean'],
            'filter' => ['sometimes', 'array'],
            'filter.name' => ['sometimes', 'string', 'max:255'],
            'filter.guard_name' => ['sometimes', 'string', 'max:255'],
            'filter.role' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Normalize raw string inputs before validation runs.
     *
     * - `direction`    → lowercased string
     * - `include`      → CSV string split into trimmed array
     * - `fields`       → CSV string split into trimmed array
     * - `only_trashed` → cast to boolean via FILTER_VALIDATE_BOOLEAN
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('direction')) {
            $merge['direction'] = strtolower((string) $this->input('direction'));
        }

        if ($this->has('include') && is_string($this->input('include'))) {
            $merge['include'] = collect(explode(',', (string) $this->input('include')))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->values()
                ->all();
        }

        if ($this->has('fields') && is_string($this->input('fields'))) {
            $merge['fields'] = collect(explode(',', (string) $this->input('fields')))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->values()
                ->all();
        }

        if ($this->has('only_trashed')) {
            $merge['only_trashed'] = filter_var($this->input('only_trashed'), FILTER_VALIDATE_BOOLEAN);
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }
}
