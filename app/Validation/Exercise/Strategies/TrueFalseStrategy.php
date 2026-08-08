<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrueFalseStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void
    {
        $request->merge(['options' => ['True', 'False']]);
    }

    public function rules(Request $request): array
    {
        return [
            'solution' => ['required', 'array', 'size:1', Rule::in(['True', 'False'])],
        ];
    }
}
