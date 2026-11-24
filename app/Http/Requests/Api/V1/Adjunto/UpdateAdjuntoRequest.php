<?php

namespace App\Http\Requests\Api\V1\Adjunto;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates input for updating an existing adjunto's metadata.
 */
class UpdateAdjuntoRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:adjunto.update` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating adjunto metadata.
     *
     * Only the human-readable description can be changed. Storage paths are internal.
     * Physical file replacement is not supported; delete and re-create instead.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'url' => ['prohibited'],
            'descripcion' => ['sometimes', 'string'],
        ];
    }
}
