<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'service' => ['nullable', 'string', 'max:150'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'actif' => ['sometimes', 'boolean'],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', Password::min(8)->letters()->numbers()],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'e-mail',
            'nom' => 'nom',
            'prenom' => 'prénom',
            'telephone' => 'téléphone',
            'service' => 'service',
            'role' => 'rôle',
            'password' => 'mot de passe',
        ];
    }
}
