<?php

namespace App\Http\Requests\Api\V1\Producto;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing producto.
 */
class UpdateProductoRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:producto.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing producto.
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
