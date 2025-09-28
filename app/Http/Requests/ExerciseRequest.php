<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Foundation\Http\FormRequest;

class ExerciseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Exercise::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('exercise'));
        }

        return false;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:6', 'max:255'],
            'options' => ['array', 'max:4'],
            'solution' => ['array', 'max:4'],
            'explanation' => ['nullable', 'string', 'min:6', 'max:255'],
            'exercise_type_id' => ['required', 'exists:exercise_types,id'],
            'lesson_id' => ['required', 'exists:lessons,id'],
        ];
    }
}
