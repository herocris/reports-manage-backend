<?php

namespace App\Http\Requests\Api\V1\Informe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for listing informes.
 *
 * Supports pagination, sorting, search, filtering, includes, field selection, and only_trashed scope.
 */
class IndexInformeRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:informe.view` check before this request is instantiated.
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
     * Accepted keys: `page`, `per_page` (1–100), `sort` (id|expediente|titulo|producto_id|fecha_inicio|fecha_final|created_at|updated_at),
     * `direction` (asc|desc), `search` (max 255), `include[]` (producto|personas|bienes|adjuntos|ubicaciones|observaciones|bitacoras|ambitos|usuarioCreador),
     * `fields[]`, `only_trashed` (bool), `filter[expediente]`, `filter[titulo]`, `filter[producto]`,
     * `filter[producto_id]`, `filter[fecha_inicio]` (date), `filter[fecha_final]` (date).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['id', 'expediente', 'titulo', 'producto_id', 'fecha_inicio', 'fecha_final', 'created_at', 'updated_at'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['sometimes', 'string', 'max:255'],
            'include' => ['sometimes', 'array'],
            'include.*' => ['string', Rule::in(['producto', 'personas', 'bienes', 'adjuntos', 'ubicaciones', 'observaciones', 'bitacoras', 'ambitos', 'usuarioCreador'])],
            'fields' => ['sometimes', 'array'],
            'fields.*' => ['string', Rule::in(['id', 'expediente', 'titulo', 'producto_id', 'fecha_inicio', 'fecha_final', 'usuariocreador_id', 'created_at', 'updated_at'])],
            'only_trashed' => ['sometimes', 'boolean'],
            'filter' => ['sometimes', 'array'],
            'filter.expediente' => ['sometimes', 'string', 'max:255'],
            'filter.titulo' => ['sometimes', 'string', 'max:255'],
            'filter.producto' => ['sometimes', 'string', 'max:255'],
            'filter.producto_id' => ['sometimes', 'integer', 'exists:productos,id'],
            'filter.fecha_inicio' => ['sometimes', 'date'],
            'filter.fecha_final' => ['sometimes', 'date'],
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
