<?php

namespace App\Http\Requests\Api\V1\Ambito;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new ambito.
 */
class StoreAmbitoRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:ambito.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new ambito.
     *
     * Required keys: `nombre` (string, max 255), `descripcion` (string, free-form text).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
        ];
    }
}
