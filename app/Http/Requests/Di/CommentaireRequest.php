<?php

namespace App\Http\Requests\Di;

use Illuminate\Foundation\Http\FormRequest;

class CommentaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenu' => ['required', 'string', 'min:5', 'max:2000'],
            'interne' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['contenu' => 'commentaire'];
    }
}
