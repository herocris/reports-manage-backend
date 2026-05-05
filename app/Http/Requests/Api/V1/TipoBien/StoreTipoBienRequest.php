<?php

namespace App\Http\Requests\Api\V1\TipoBien;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for creating a new tipo de bien.
 */
class StoreTipoBienRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:tipobien.create` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new tipo de bien.
     *
     * Required: `nombre` (string, max 255 — the category name),
     * `descripcion` (string — free-form description of the asset category).
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
