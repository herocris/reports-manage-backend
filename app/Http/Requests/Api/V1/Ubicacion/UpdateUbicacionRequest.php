<?php

namespace App\Http\Requests\Api\V1\Ubicacion;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing ubicacion.
 */
class UpdateUbicacionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:ubicacion.update` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing ubicacion.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `latitud` (numeric, -90 to 90, up to 6 decimal places),
     * `longitud` (numeric, -180 to 180, up to 6 decimal places),
     * `descripcion` (string), `informe_id` (integer, must exist in `informes`).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'latitud' => ['sometimes', 'numeric', 'between:-90,90', 'decimal:0,6'],
            'longitud' => ['sometimes', 'numeric', 'between:-180,180', 'decimal:0,6'],
            'descripcion' => ['sometimes', 'string'],
            'informe_id' => ['sometimes', 'integer', 'exists:informes,id'],
        ];
    }
}
