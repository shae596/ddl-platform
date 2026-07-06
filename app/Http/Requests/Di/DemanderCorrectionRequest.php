<?php

namespace App\Http\Requests\Di;

use Illuminate\Foundation\Http\FormRequest;

class DemanderCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commentaire' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['commentaire' => 'commentaire'];
    }

    public function messages(): array
    {
        return [
            'commentaire.required' => 'Le commentaire est obligatoire.',
            'commentaire.min' => 'Le commentaire doit contenir au moins 20 caractères.',
        ];
    }
}
