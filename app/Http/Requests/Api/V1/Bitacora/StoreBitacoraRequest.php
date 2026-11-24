<?php

namespace App\Http\Requests\Api\V1\Bitacora;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new bitacora entry.
 */
class StoreBitacoraRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:bitacora.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new bitacora entry.
     *
     * Required: `descripcion` (string — log entry text),
     * `fecha` (date — entry date in any parseable format),
     * `informe_id` (integer, must exist in `informes` — the parent report).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'descripcion' => ['required', 'string'],
            'fecha' => ['required', 'date'],
            'informe_id' => ['required', 'integer', 'exists:informes,id'],
        ];
    }
}
