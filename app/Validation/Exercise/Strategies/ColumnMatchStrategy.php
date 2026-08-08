<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;

class ColumnMatchStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void
    {
    }

    public function rules(Request $request): array
    {
        return [
            'options' => ['required', 'array', 'min:2'],
            'solution' => ['required', 'array', 'min:1'],
        ];
    }
}
