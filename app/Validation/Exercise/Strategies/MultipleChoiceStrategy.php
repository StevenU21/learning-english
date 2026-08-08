<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MultipleChoiceStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void
    {
        // No specific mutation needed for multiple choice
    }

    public function rules(Request $request): array
    {
        return [
            'options' => ['required', 'array', 'min:2'],
            'solution' => ['required', 'array', 'min:1'],
            'solution.*' => [Rule::in($request->input('options', []))],
        ];
    }
}
