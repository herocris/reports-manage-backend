<?php

namespace App\Http\Requests\Api\V1\Observacion;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new observacion.
 */
class StoreObservacionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:observacion.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new observacion.
     *
     * Required: `descripcion` (string — free-form observation text),
     * `usuario_id` (integer, must exist in `users` — the user who made the observation),
     * `informe_id` (integer, must exist in `informes` — the parent report).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'informe_id' => ['required', 'integer', 'exists:informes,id'],
        ];
    }
}
