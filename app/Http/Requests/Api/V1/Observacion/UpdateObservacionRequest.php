<?php

namespace App\Http\Requests\Api\V1\Observacion;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing observacion.
 */
class UpdateObservacionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:observacion.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing observacion.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `descripcion` (string), `usuario_id` (integer, must exist in `users`),
     * `informe_id` (integer, must exist in `informes`).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'descripcion' => ['sometimes', 'string'],
            'usuario_id'  => ['sometimes', 'integer', 'exists:users,id'],
            'informe_id'  => ['sometimes', 'integer', 'exists:informes,id'],
        ];
    }
}
