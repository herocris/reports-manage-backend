<?php

namespace App\Http\Requests\Api\V1\TipoBien;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing tipo de bien.
 */
class UpdateTipoBienRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:tipobien.update` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing tipo de bien.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `nombre` (string, max 255), `descripcion` (string).
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
