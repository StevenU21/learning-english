<?php

namespace App\Http\Requests;

use App\Enums\ExerciseTypeEnum;
use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Validation\Exercise\ExerciseValidationFactory;
use Illuminate\Foundation\Http\FormRequest;

class ExerciseRequest extends FormRequest
{
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

    protected function prepareForValidation(): void
    {
        $explanation = $this->input('explanation');
        if (is_string($explanation) && trim($explanation) === '') {
            $this->merge(['explanation' => null]);
        }

        $options = collect($this->input('options', []))
            ->filter(fn ($v) => ! is_null($v) && ! (is_string($v) && trim($v) === ''))
            ->values()->all();
        $this->merge(['options' => $options]);

        $solution = collect($this->input('solution', []))
            ->filter(fn ($v) => ! is_null($v) && ! (is_string($v) && trim($v) === ''))
            ->values()->all();
        $this->merge(['solution' => $solution]);

        $exerciseType = ExerciseType::find($this->input('exercise_type_id'));
        $typeEnum = $exerciseType ? ExerciseTypeEnum::tryFrom($exerciseType->name) : null;

        $strategy = ExerciseValidationFactory::make($typeEnum);
        $strategy->prepareForValidation($this);
    }

    public function rules(): array
    {
        $baseRules = [
            'prompt' => ['nullable', 'string', 'min:6', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:20480'],
            'file_b' => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:20480'],
            'options' => ['array', 'max:10'],
            'solution' => ['array'],
            'explanation' => ['nullable', 'string', 'min:6', 'max:255'],
            'exercise_type_id' => ['required', 'exists:exercise_types,id'],
            'lesson_id' => ['required', 'exists:lessons,id'],
        ];

        $exerciseType = ExerciseType::find($this->input('exercise_type_id'));
        $typeEnum = $exerciseType ? ExerciseTypeEnum::tryFrom($exerciseType->name) : null;

        $strategy = ExerciseValidationFactory::make($typeEnum);
        $specificRules = $strategy->rules($this);

        return array_merge($baseRules, $specificRules);
    }

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

    public function messages(): array
    {
        return [
            'prompt.required' => 'El enunciado es obligatorio.',
            'solution.required' => 'Debes ingresar la solución.',
            'options.min' => 'Debes agregar al menos :min opciones.',
            'solution.in' => 'La solución no está entre las opciones permitidas.',
            'solution.*.in' => 'La solución debe estar entre las opciones.',
            'file.required' => 'Debes subir un archivo de audio.',
            'file_b.required' => 'Debes subir el segundo audio.',
        ];
    }
}
