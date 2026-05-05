<?php

namespace App\Http\Requests\Api\V1\Adjunto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * Validates input for uploading one or more adjunto files.
 *
 * Requires a `tipo` and `modelo_id` to identify the polymorphic owner.
 */
class StoreAdjuntoRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:adjunto.create` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for uploading adjunto files.
     *
     * Required keys: `tipo` (string, one of: persona|bien|informe),
     * `modelo_id` (integer — PK of the polymorphic owner), `archivos` (array, min 1 file),
     * `archivos.*` (image file, max 10 MB each).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'in:persona,bien,informe'],
            'modelo_id' => ['required', 'integer'],
            'archivos' => ['required', 'array', 'min:1'],
            'archivos.*' => ['required', File::image()->max('10mb')],
        ];
    }
}
