<?php

namespace App\Http\Requests\Api\V1\Ubicacion;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new ubicacion.
 */
class StoreUbicacionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:ubicacion.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new ubicacion.
     *
     * Required: `latitud` (numeric, -90 to 90, up to 6 decimal places),
     * `longitud` (numeric, -180 to 180, up to 6 decimal places),
     * `descripcion` (string — human-readable location description),
     * `informe_id` (integer, must exist in `informes` — the parent report).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'latitud' => ['required', 'numeric', 'between:-90,90', 'decimal:0,6'],
            'longitud' => ['required', 'numeric', 'between:-180,180', 'decimal:0,6'],
            'descripcion' => ['required', 'string'],
            'informe_id' => ['required', 'integer', 'exists:informes,id'],
        ];
    }
}
