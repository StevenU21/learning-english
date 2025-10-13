<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,png,jpeg,webp',
                'max:4096',
            ],
            'nickname' => [
                'nullable',
                'string',
                'max:255',
            ],
            'birthdate' => [
                'nullable',
                'date',
            ],
            'daily_goal_minutes' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'gender' => [
                'nullable',
                'string',
                Rule::in(['male', 'female']),
            ],
        ];
    }

    /**
     * Custom attribute names in Spanish.
     *
     * @return array<string,string>
     */
    public function attributes(): array
    {
        return [
            'avatar' => 'avatar',
            'nickname' => 'apodo',
            'birthdate' => 'fecha de nacimiento',
            'daily_goal_minutes' => 'meta diaria (minutos)',
            'total_minutes' => 'minutos totales',
            'streak_days' => 'racha de días',
            'gender' => 'género',
        ];
    }

    /**
     * Custom validation messages in Spanish.
     *
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'avatar.image' => 'El :attribute debe ser una imagen.',
            'avatar.mimes' => 'El :attribute debe ser un archivo de tipo: :values.',
            'avatar.max'   => 'El :attribute no debe pesar más de :max kilobytes.',
            'nickname.string' => 'El apodo debe ser una cadena de texto.',
            'nickname.max'    => 'El apodo no debe exceder de :max caracteres.',
            'birthdate.date'  => 'La fecha de nacimiento no es válida.',
            'daily_goal_minutes.integer' => 'La :attribute debe ser un número entero.',
            'daily_goal_minutes.min' => 'La :attribute no puede ser negativa.',
            'gender.in'         => 'El género seleccionado no es válido.',
        ];
    }
}
