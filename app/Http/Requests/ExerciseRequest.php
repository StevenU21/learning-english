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
            'file' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/wav,audio/ogg', 'max:20480'],
            'file_b' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/wav,audio/ogg', 'max:20480'],
            'options' => ['array', 'max:4'],
            'solution' => ['array', 'max:4'],
            'explanation' => ['nullable', 'string', 'min:6', 'max:255'],
            'exercise_type_id' => ['required', 'exists:exercise_types,id'],
            'lesson_id' => ['required', 'exists:lessons,id'],
        ];
    }
    /**
     * Custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'prompt' => 'enunciado',
            'file' => 'audio',
            'file_b' => 'segundo audio',
            'options' => 'opciones',
            'solution' => 'solución',
            'explanation' => 'explicación',
            'exercise_type_id' => 'tipo de ejercicio',
            'lesson_id' => 'lección',
        ];
    }

    /**
     * Custom validation messages in Spanish.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prompt.required' => 'El enunciado es obligatorio.',
            'prompt.string'   => 'El enunciado debe ser una cadena de texto.',
            'prompt.min'      => 'El enunciado debe tener al menos :min caracteres.',
            'prompt.max'      => 'El enunciado no debe exceder de :max caracteres.',
            'options.array'   => 'Las opciones deben ser un arreglo.',
            'options.max'     => 'Las opciones no pueden ser más de :max.',
            'solution.array'  => 'La solución debe ser un arreglo.',
            'solution.max'    => 'La solución no puede ser más de :max.',
            'explanation.string' => 'La explicación debe ser una cadena de texto.',
            'explanation.min'    => 'La explicación debe tener al menos :min caracteres.',
            'explanation.max'    => 'La explicación no debe exceder de :max caracteres.',
            'exercise_type_id.required' => 'El tipo de ejercicio es obligatorio.',
            'exercise_type_id.exists'   => 'El tipo de ejercicio seleccionado no es válido.',
            'lesson_id.required' => 'La lección es obligatoria.',
            'lesson_id.exists'   => 'La lección seleccionada no es válida.',

            'file.file' => 'El archivo debe ser un archivo válido.',
            'file.mimetypes' => 'El audio debe ser MP3, WAV u OGG.',
            'file.max' => 'El audio no debe superar los :max kilobytes.',
            'file_b.file' => 'El segundo archivo debe ser un archivo válido.',
            'file_b.mimetypes' => 'El segundo audio debe ser MP3, WAV u OGG.',
            'file_b.max' => 'El segundo audio no debe superar los :max kilobytes.',
        ];
    }
}
