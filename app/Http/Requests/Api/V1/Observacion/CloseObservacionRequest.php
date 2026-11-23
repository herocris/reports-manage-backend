<?php

namespace App\Http\Requests\Api\V1\Observacion;

use App\Models\Observacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CloseObservacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $observacion = $this->route('observacion');

        return $observacion instanceof Observacion
            && $this->user()?->id === $observacion->informe->usuariocreador_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contenido' => ['required', 'string'],
        ];
    }
}
