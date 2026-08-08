<?php

namespace App\Validation\Exercise;

use Illuminate\Http\Request;

interface ExerciseValidationStrategy
{
    /**
     * Get the validation rules specific to this exercise type.
     */
    public function rules(Request $request): array;

    /**
     * Mutate or prepare data before validation runs.
     */
    public function prepareForValidation(Request $request): void;
}
