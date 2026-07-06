<?php

namespace App\Http\Requests\Di;

use Illuminate\Foundation\Http\FormRequest;

class RejeterDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motif_rejet' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['motif_rejet' => 'motif de rejet'];
    }

    public function messages(): array
    {
        return [
            'motif_rejet.required' => 'Le motif de rejet est obligatoire.',
            'motif_rejet.min' => 'Le motif de rejet doit contenir au moins 20 caractères.',
        ];
    }
}
