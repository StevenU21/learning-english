<?php

namespace App\Http\Requests;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Si se envía un batch de intentos
        if ($this->has('attempts')) {
            return [
                'attempts' => 'required|array|min:1',
                'attempts.*.exercise_id' => 'required|exists:exercises,id',
                'attempts.*.answer_given' => 'required',
                'attempts.*.is_correct' => 'required|boolean',
                'attempts.*.attempt_number' => 'required|integer',
            ];
        }
        // Si es un solo intento
        return [
            'exercise_id' => 'required|exists:exercises,id',
            'answer_given' => 'required',
            'is_correct' => 'required|boolean',
            'attempt_number' => 'required|integer',
        ];
    }
}
