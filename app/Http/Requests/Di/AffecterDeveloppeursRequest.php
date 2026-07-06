<?php

namespace App\Http\Requests\Di;

use Illuminate\Foundation\Http\FormRequest;

class AffecterDeveloppeursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'developpeur_ids' => ['required', 'array', 'min:1'],
            'developpeur_ids.*' => ['required', 'uuid', 'exists:users,id'],
        ];
    }

    public function attributes(): array
    {
        return ['developpeur_ids' => 'développeurs'];
    }

    public function messages(): array
    {
        return [
            'developpeur_ids.required' => 'Sélectionnez au moins un développeur.',
            'developpeur_ids.min' => 'Sélectionnez au moins un développeur.',
        ];
    }
}
