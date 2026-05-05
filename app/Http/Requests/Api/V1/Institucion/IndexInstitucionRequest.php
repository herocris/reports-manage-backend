<?php

namespace App\Http\Requests\Api\V1\Institucion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for listing instituciones.
 *
 * Supports pagination, sorting, search, filtering, includes, field selection, and only_trashed scope.
 */
class IndexInstitucionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:institucion.view` check before this request is instantiated.
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
     * Accepted keys: `page`, `per_page` (1–100), `sort` (id|nombre|contacto|telefono|created_at|updated_at),
     * `direction` (asc|desc), `search` (max 255), `fields[]`, `only_trashed` (bool),
     * `filter[nombre]`, `filter[contacto]`, `filter[telefono]`.
     * Note: `include` is not supported for instituciones.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['id', 'nombre', 'contacto', 'telefono', 'created_at', 'updated_at'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['sometimes', 'string', 'max:255'],
            'fields' => ['sometimes', 'array'],
            'fields.*' => ['string', Rule::in(['id', 'nombre', 'contacto', 'telefono', 'created_at', 'updated_at'])],
            'only_trashed' => ['sometimes', 'boolean'],
            'filter' => ['sometimes', 'array'],
            'filter.nombre' => ['sometimes', 'string', 'max:255'],
            'filter.contacto' => ['sometimes', 'string', 'max:255'],
            'filter.telefono' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Normalize raw string inputs before validation runs.
     *
     * - `direction` → lowercased string
     * - `fields`    → CSV string split into trimmed array
     * - `only_trashed` → cast to boolean via FILTER_VALIDATE_BOOLEAN
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('direction')) {
            $merge['direction'] = strtolower((string) $this->input('direction'));
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
