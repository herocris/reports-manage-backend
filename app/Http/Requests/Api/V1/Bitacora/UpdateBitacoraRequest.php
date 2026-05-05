<?php

namespace App\Http\Requests\Api\V1\Bitacora;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing bitacora entry.
 */
class UpdateBitacoraRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:bitacora.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing bitacora entry.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `descripcion` (string), `fecha` (date), `informe_id` (integer, must exist in `informes`).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'descripcion' => ['sometimes', 'string'],
            'fecha'       => ['sometimes', 'date'],
            'informe_id'  => ['sometimes', 'integer', 'exists:informes,id'],
        ];
    }
}
