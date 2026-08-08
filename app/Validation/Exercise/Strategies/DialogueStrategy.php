<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;

class DialogueStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void {}

    public function rules(Request $request): array
    {
        return [
            'options' => ['required', 'array', 'min:1'],
            'solution' => ['required', 'array', 'min:1'],
        ];
    }
}
