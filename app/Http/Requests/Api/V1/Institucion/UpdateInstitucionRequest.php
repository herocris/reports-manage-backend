<?php

namespace App\Http\Requests\Api\V1\Institucion;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing institucion.
 */
class UpdateInstitucionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:institucion.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing institucion.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `nombre` (string, max 255), `contacto` (string, max 255),
     * `telefono` (string, regex \d{4}-\d{4}).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'contacto' => ['sometimes', 'string', 'max:255'],
            'telefono' => ['sometimes', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ];
    }
}
