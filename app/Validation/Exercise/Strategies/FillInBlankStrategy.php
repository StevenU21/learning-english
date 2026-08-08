<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;

class FillInBlankStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void
    {
    }

    public function rules(Request $request): array
    {
        return [
            'solution' => ['required', 'array', 'min:1'],
        ];
    }
}
