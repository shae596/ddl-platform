<?php

namespace App\Http\Requests\Di;

use Illuminate\Foundation\Http\FormRequest;

class DelaiPrevisionnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delai_previsionnel' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function attributes(): array
    {
        return ['delai_previsionnel' => 'délai prévisionnel'];
    }
}
