<?php

namespace App\Http\Requests\Api\V1\Persona;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates input for updating an existing persona.
 */
class UpdatePersonaRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:persona.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing persona.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `nombre` (string, max 255), `numero_identificacion` (string, regex \d{4}-\d{4}-\d{5},
     * unique ignoring the current record).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'numero_identificacion' => [
                'sometimes',
                'string',
                'regex:/^\d{4}-\d{4}-\d{5}$/',
                Rule::unique('personas', 'numero_identificacion')->ignore($this->route('persona')?->id),
            ],
        ];
    }
}
