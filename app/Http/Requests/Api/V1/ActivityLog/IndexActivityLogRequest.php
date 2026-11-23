<?php

namespace App\Http\Requests\Api\V1\ActivityLog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for listing activity logs.
 *
 * Supports pagination, sorting, search, and filtering.
 */
class IndexActivityLogRequest extends FormRequest
{
    /**
     * Grant access to authenticated users; route middleware enforces the
     * `permission:activity-log.view` check before this request is instantiated.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in([
                'id',
                'log_name',
                'description',
                'event',
                'subject_type',
                'subject_id',
                'causer_id',
                'created_at',
                'updated_at',
            ])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'search' => ['sometimes', 'string', 'max:255'],
            'filter' => ['sometimes', 'array'],
            'filter.subject_type' => ['sometimes', 'string', 'max:255'],
            'filter.subject_id' => ['sometimes', 'integer', 'min:1'],
            'filter.event' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Normalize the sorting direction before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('direction')) {
            $this->merge([
                'direction' => strtolower((string) $this->input('direction')),
            ]);
        }
    }
}
