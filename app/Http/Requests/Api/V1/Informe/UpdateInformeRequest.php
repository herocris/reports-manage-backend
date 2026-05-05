<?php

namespace App\Http\Requests\Api\V1\Informe;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing informe.
 */
class UpdateInformeRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:informe.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing informe.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `expediente` (unique ignoring self, regex EXP-[A-Z]{3}\d{3}), `titulo` (string max 255),
     * `producto_id` (integer, must exist in `productos`), `fecha_inicio` (date),
     * `fecha_final` (date, >= `fecha_inicio`), `ambitos` (array of IDs — full sync).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'expediente'       => ['sometimes', 'string', 'regex:/^EXP-[A-Z]{3}\d{3}$/', 'unique:informes,expediente,' . $this->route('informe')?->id],
            'titulo'           => ['sometimes', 'string', 'max:255'],
            'producto_id'      => ['sometimes', 'integer', 'exists:productos,id'],
            'fecha_inicio'     => ['sometimes', 'date'],
            'fecha_final'      => ['sometimes', 'date', 'after_or_equal:fecha_inicio'],
            'ambitos'          => ['sometimes', 'array'],
            'ambitos.*'        => ['integer', 'exists:ambitos,id'],
        ];
    }
}
