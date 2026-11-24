<?php

namespace App\Http\Requests\Api\V1\Institucion;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new institucion.
 */
class StoreInstitucionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:institucion.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new institucion.
     *
     * Required: `nombre` (string, max 255), `contacto` (string, max 255 — contact person name),
     * `telefono` (string, regex \d{4}-\d{4} — local phone format).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'contacto' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ];
    }
}
