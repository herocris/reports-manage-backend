<?php

namespace App\Http\Requests\Api\V1\Informe;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new informe.
 */
class StoreInformeRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:informe.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new informe.
     *
     * Required: `expediente` (unique string, regex EXP-[A-Z]{3}\d{3}), `titulo` (string max 255),
     * `producto_id` (integer, must exist in `productos`),
     * `fecha_inicio` (date), `fecha_final` (date, must be >= `fecha_inicio`).
     * Optional: `ambitos` (array of integer IDs, must exist in `ambitos` — sync replaces all current links).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'expediente' => ['required', 'string', 'regex:/^EXP-[A-Z]{3}\d{3}$/', 'unique:informes,expediente'],
            'titulo' => ['required', 'string', 'max:255'],
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_final' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'ambitos' => ['sometimes', 'array'],
            'ambitos.*' => ['integer', 'exists:ambitos,id'],
        ];
    }
}
