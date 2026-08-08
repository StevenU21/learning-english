<?php

namespace App\Services;

use App\DTOs\ExerciseDTO;
use App\Models\Exercise;
use Illuminate\Support\Facades\DB;

class ExerciseService
{
    public function createExercise(ExerciseDTO $dto): Exercise
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();

            return Exercise::create($data);
        });
    }

    public function updateExercise(Exercise $exercise, ExerciseDTO $dto): Exercise
    {
        return DB::transaction(function () use ($exercise, $dto) {
            $data = $dto->toArray();

            if ($data['file'] === null) {
                unset($data['file']);
            }
            if ($data['file_b'] === null) {
                unset($data['file_b']);
            }

            $exercise->update($data);

            return $exercise;
        });
    }
}
