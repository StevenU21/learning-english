<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListenRespondStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void
    {
        $request->merge(['options' => ['Igual', 'Distinto']]);
    }

    public function rules(Request $request): array
    {
        return [
            'file' => [Rule::requiredIf($request->isMethod('post')), 'file', 'mimes:mp3,wav,ogg'],
            'file_b' => [Rule::requiredIf($request->isMethod('post')), 'file', 'mimes:mp3,wav,ogg'],
            'solution' => ['required', 'array', 'size:1', Rule::in(['Igual', 'Distinto'])],
        ];
    }
}
