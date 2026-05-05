<?php

namespace App\Http\Requests\Api\V1\Persona;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new persona.
 */
class StorePersonaRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:persona.create` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new persona.
     *
     * Required: `nombre` (string, max 255), `numero_identificacion` (string, regex \d{4}-\d{4}-\d{5},
     * must be unique in `personas`), `informe_id` (integer, must exist in `informes`).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'numero_identificacion' => ['required', 'string', 'regex:/^\d{4}-\d{4}-\d{5}$/', 'unique:personas,numero_identificacion'],
            'informe_id' => ['required', 'integer', 'exists:informes,id'],
        ];
    }
}
