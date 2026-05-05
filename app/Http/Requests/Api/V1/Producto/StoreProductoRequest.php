<?php

namespace App\Http\Requests\Api\V1\Producto;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new producto.
 */
class StoreProductoRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:producto.create` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new producto.
     *
     * Required: `nombre` (string, max 255), `descripcion` (string — free-form description).
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
