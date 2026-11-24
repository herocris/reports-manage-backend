<?php

namespace App\Http\Requests\Api\V1\Bien;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing bien.
 */
class UpdateBienRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:bien.update` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing bien.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `descripcion` (string), `tipobien_id` (integer, must exist in `tipo_biens`).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'descripcion' => ['sometimes', 'string'],
            'tipobien_id' => ['sometimes', 'integer', 'exists:tipo_biens,id'],
        ];
    }
}
