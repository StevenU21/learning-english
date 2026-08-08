<?php

namespace App\Validation\Exercise\Strategies;

use App\Validation\Exercise\ExerciseValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListenWriteStrategy implements ExerciseValidationStrategy
{
    public function prepareForValidation(Request $request): void {}

    public function rules(Request $request): array
    {
        return [
            'file' => [Rule::requiredIf($request->isMethod('post')), 'file', 'mimes:mp3,wav,ogg'],
            'solution' => ['required', 'array', 'min:1'],
        ];
    }
}
