<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => trim((string) $this->input('description')),
            'address' => trim((string) $this->input('address')),
            'contact_phone' => preg_replace('/[\s\-().]/', '', (string) $this->input('contact_phone')) ?? '',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:2000'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'in:morning,afternoon,evening,any'],
            'address' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Décrivez le travail à réaliser.',
            'description.max' => 'La description est trop longue (2000 caractères maximum).',
            'preferred_date.required' => 'Choisissez une date.',
            'preferred_date.date' => 'La date choisie n’est pas valide.',
            'preferred_date.after_or_equal' => 'La date doit être aujourd’hui ou plus tard.',
            'time_slot.required' => 'Choisissez un créneau.',
            'time_slot.in' => 'Choisissez un créneau valide.',
            'address.required' => 'Indiquez votre quartier ou adresse.',
            'contact_phone.required' => 'Indiquez un numéro de contact.',
        ];
    }
}
