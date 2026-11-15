<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/[\s\-().]/', '', (string) $this->input('phone')) ?? '',
            'city' => trim((string) $this->input('city')),
            'service_area' => trim((string) $this->input('service_area')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'city' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in([UserRole::Customer->value, UserRole::Worker->value])],
            'trade_id' => ['exclude_unless:role,worker', 'required_if:role,worker', 'integer', 'exists:trades,id'],
            'experience_years' => ['exclude_unless:role,worker', 'required_if:role,worker', 'integer', 'min:0', 'max:60'],
            'service_area' => ['exclude_unless:role,worker', 'required_if:role,worker', 'string', 'max:255'],
            'bio' => ['exclude_unless:role,worker', 'required_if:role,worker', 'string', 'max:1000'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email' => 'L’adresse e-mail n’est pas valide.',
            'email.unique' => 'Un compte utilise déjà cette adresse e-mail.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Un compte utilise déjà ce numéro de téléphone.',
            'city.required' => 'Choisissez votre ville ou commune.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'role.required' => 'Choisissez le type de compte.',
            'role.in' => 'Choisissez un type de compte valide.',
            'trade_id.required_if' => 'Choisissez votre métier.',
            'trade_id.exists' => 'Ce métier n’est pas disponible.',
            'experience_years.required_if' => 'Indiquez vos années d’expérience.',
            'experience_years.integer' => 'Les années d’expérience doivent être un nombre.',
            'service_area.required_if' => 'Indiquez votre zone de déplacement.',
            'bio.required_if' => 'Présentez vos services.',
            'terms.accepted' => 'Vous devez accepter les conditions d’utilisation.',
        ];
    }
}
