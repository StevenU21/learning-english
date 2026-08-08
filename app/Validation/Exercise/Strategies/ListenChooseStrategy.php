<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListenChooseStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void
    {
    }

    public function rules(Request $request): array
    {
        return [
            'file' => [Rule::requiredIf($request->isMethod('post')), 'file', 'mimes:mp3,wav,ogg'],
            'options' => ['required', 'array', 'min:2', 'max:4'],
            'solution' => ['required', 'array', 'size:1'],
            'solution.*' => [Rule::in($request->input('options', []))],
        ];
    }
}
