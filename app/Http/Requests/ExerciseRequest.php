<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

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
            'file' => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:20480'],
            'file_b' => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:20480'],
            'options' => ['array', 'max:4'],
            'solution' => ['array', 'max:4'],
            'explanation' => ['nullable', 'string', 'min:6', 'max:255'],
            'exercise_type_id' => ['required', 'exists:exercise_types,id'],
            'lesson_id' => ['required', 'exists:lessons,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        try {
            $file = $this->file('file');
            $fileB = $this->file('file_b');
            Log::info('ExerciseRequest incoming payload', [
                'method' => $this->method(),
                'exercise_type_id' => $this->input('exercise_type_id'),
                'lesson_id' => $this->input('lesson_id'),
                'has_file' => (bool) $file,
                'has_file_b' => (bool) $fileB,
                'file' => $file ? $this->fileDebugInfo($file) : null,
                'file_b' => $fileB ? $this->fileDebugInfo($fileB) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ExerciseRequest prepareForValidation logging failed', ['error' => $e->getMessage()]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->fails()) {
                Log::warning('ExerciseRequest validation failed', [
                    'errors' => $validator->errors()->toArray(),
                ]);
            } else {
                Log::info('ExerciseRequest validation passed');
            }
        });
    }

    private function fileDebugInfo(\Illuminate\Http\UploadedFile $file): array
    {
        return [
            'original_name' => $file->getClientOriginalName(),
            'client_mime' => $file->getClientMimeType(),
            'detected_mime' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size_kb' => round(($file->getSize() ?? 0) / 1024, 2),
            'is_valid' => method_exists($file, 'isValid') ? $file->isValid() : null,
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
            'prompt.string' => 'El enunciado debe ser una cadena de texto.',
            'prompt.min' => 'El enunciado debe tener al menos :min caracteres.',
            'prompt.max' => 'El enunciado no debe exceder de :max caracteres.',
            'options.array' => 'Las opciones deben ser un arreglo.',
            'options.max' => 'Las opciones no pueden ser más de :max.',
            'solution.array' => 'La solución debe ser un arreglo.',
            'solution.max' => 'La solución no puede ser más de :max.',
            'explanation.string' => 'La explicación debe ser una cadena de texto.',
            'explanation.min' => 'La explicación debe tener al menos :min caracteres.',
            'explanation.max' => 'La explicación no debe exceder de :max caracteres.',
            'exercise_type_id.required' => 'El tipo de ejercicio es obligatorio.',
            'exercise_type_id.exists' => 'El tipo de ejercicio seleccionado no es válido.',
            'lesson_id.required' => 'La lección es obligatoria.',
            'lesson_id.exists' => 'La lección seleccionada no es válida.',
            'file.file' => 'El archivo debe ser un archivo válido.',
            'file.mimes' => 'El audio debe ser MP3, WAV u OGG.',
            'file.max' => 'El audio no debe superar los :max kilobytes.',
            'file_b.file' => 'El segundo archivo debe ser un archivo válido.',
            'file_b.mimes' => 'El segundo audio debe ser MP3, WAV u OGG.',
            'file_b.max' => 'El segundo audio no debe superar los :max kilobytes.',
        ];
    }
}
