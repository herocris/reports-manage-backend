<?php

namespace App\Http\Requests\Api\V1\Bien;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new bien.
 */
class StoreBienRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:bien.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new bien.
     *
     * Required: `descripcion` (string — free-form description of the asset),
     * `tipobien_id` (integer, must exist in `tipo_biens`),
     * `informe_id` (integer, must exist in `informes` — the owning report).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'descripcion' => ['required', 'string'],
            'tipobien_id' => ['required', 'integer', 'exists:tipo_biens,id'],
            'informe_id' => ['required', 'integer', 'exists:informes,id'],
        ];
    }
}
