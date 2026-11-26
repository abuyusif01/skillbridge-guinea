<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isWorker();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bio' => trim((string) $this->input('bio')),
            'service_area' => trim((string) $this->input('service_area')),
            'is_available' => $this->boolean('is_available'),
            'rate_min' => $this->filled('rate_min') ? $this->input('rate_min') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'trade_id' => ['required', 'integer', 'exists:trades,id'],
            'bio' => ['required', 'string', 'max:1000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'service_area' => ['required', 'string', 'max:255'],
            'rate_min' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'is_available' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'trade_id.required' => 'Choisissez votre métier.',
            'trade_id.exists' => 'Ce métier n’est pas disponible.',
            'bio.required' => 'Présentez vos services.',
            'bio.max' => 'La présentation est trop longue (1000 caractères maximum).',
            'experience_years.required' => 'Indiquez vos années d’expérience.',
            'experience_years.integer' => 'Les années d’expérience doivent être un nombre.',
            'experience_years.min' => 'Les années d’expérience ne peuvent pas être négatives.',
            'experience_years.max' => 'Indiquez au maximum 60 ans d’expérience.',
            'service_area.required' => 'Indiquez votre zone de déplacement.',
            'service_area.max' => 'La zone de déplacement est trop longue.',
            'rate_min.integer' => 'Le tarif doit être un nombre entier en GNF.',
            'rate_min.min' => 'Le tarif ne peut pas être négatif.',
            'rate_min.max' => 'Le tarif indiqué est trop élevé.',
        ];
    }
}
