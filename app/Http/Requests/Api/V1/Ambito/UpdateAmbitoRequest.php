<?php

namespace App\Http\Requests\Api\V1\Ambito;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing ambito.
 */
class UpdateAmbitoRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:ambito.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing ambito.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `nombre` (string, max 255), `descripcion` (string, free-form text).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'string'],
        ];
    }
}
