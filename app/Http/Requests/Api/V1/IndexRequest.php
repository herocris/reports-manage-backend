<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    abstract protected function definition(): array;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $definition = $this->definition();
        $filters = $definition['filters'] ?? [];

        $rules = [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in($definition['sortable'] ?? [])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['sometimes', 'string', 'max:255'],
            'filter' => ['sometimes', 'array:'.implode(',', array_keys($filters))],
        ];

        foreach ($filters as $field => $filter) {
            $rules['filter.'.$field] = $filter['rules'];
        }

        foreach (['include' => 'includes', 'fields' => 'fields'] as $parameter => $key) {
            if (array_key_exists($key, $definition)) {
                $rules[$parameter] = ['sometimes', 'array'];
                $rules[$parameter.'.*'] = ['string', Rule::in($definition[$key])];
            }
        }

        if ($definition['soft_deletes'] ?? true) {
            $rules['only_trashed'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if (is_string($this->input('direction'))) {
            $normalized['direction'] = strtolower($this->input('direction'));
        }

        foreach (['include', 'fields'] as $parameter) {
            if (is_string($this->input($parameter))) {
                $normalized[$parameter] = array_values(array_filter(
                    array_map(trim(...), explode(',', $this->input($parameter))),
                    fn (string $value): bool => $value !== '',
                ));
            }
        }

        if ($this->has('only_trashed')) {
            $normalized['only_trashed'] = filter_var(
                $this->input('only_trashed'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE,
            );
        }

        $this->merge($normalized);
    }
}
