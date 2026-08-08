<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserExerciseAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Si se envía un batch de intentos
        if ($this->has('attempts')) {
            return [
                'attempts' => ['required', 'array', 'min:1'],
                'attempts.*.exercise_id' => ['required', 'exists:exercises,id'],
                'attempts.*.answer_given' => ['required'],
                'attempts.*.is_correct' => ['required', 'boolean'],
                'attempts.*.attempt_number' => ['required', 'integer'],
            ];
        }

        // Si es un solo intento
        return [
            'exercise_id' => ['required', 'exists:exercises,id'],
            'answer_given' => ['required'],
            'is_correct' => ['required', 'boolean'],
            'attempt_number' => ['required', 'integer'],
        ];
    }

    /**
     * Nombres de atributos personalizados en español.
     *
     * @return array<string,string>
     */
    public function attributes(): array
    {
        return [
            'attempts' => 'intentos',
            'attempts.*.exercise_id' => 'ejercicio',
            'attempts.*.answer_given' => 'respuesta dada',
            'attempts.*.is_correct' => 'es correcto',
            'attempts.*.attempt_number' => 'número de intento',
            'exercise_id' => 'ejercicio',
            'answer_given' => 'respuesta dada',
            'is_correct' => 'es correcto',
            'attempt_number' => 'número de intento',
        ];
    }

    /**
     * Mensajes de validación personalizados en español.
     *
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'attempts.required' => 'Debe enviar al menos un intento.',
            'attempts.array' => 'Los intentos deben ser un arreglo.',
            'attempts.min' => 'Debe haber al menos :min intento.',
            'attempts.*.exercise_id.required' => 'El campo :attribute es obligatorio.',
            'attempts.*.exercise_id.exists' => 'El ejercicio seleccionado no es válido.',
            'attempts.*.answer_given.required' => 'La :attribute es obligatoria.',
            'attempts.*.is_correct.required' => 'El campo :attribute es obligatorio.',
            'attempts.*.is_correct.boolean' => 'El campo :attribute debe ser verdadero o falso.',
            'attempts.*.attempt_number.required' => 'El campo :attribute es obligatorio.',
            'attempts.*.attempt_number.integer' => 'El campo :attribute debe ser un número entero.',
            'exercise_id.required' => 'El ejercicio es obligatorio.',
            'exercise_id.exists' => 'El ejercicio seleccionado no es válido.',
            'answer_given.required' => 'La respuesta es obligatoria.',
            'is_correct.required' => 'El campo es correcto es obligatorio.',
            'is_correct.boolean' => 'El campo es correcto debe ser verdadero o falso.',
            'attempt_number.required' => 'El número de intento es obligatorio.',
            'attempt_number.integer' => 'El número de intento debe ser un número entero.',
        ];
    }
}
