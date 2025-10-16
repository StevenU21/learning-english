<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonUserProgress;

class LessonProgressService
{
    public function updateFromAttempts(int $userId, Lesson $lesson, array $attempts): array
    {
        if (!$lesson->relationLoaded('exercises')) {
            $lesson->load('exercises');
        }

        $total = $lesson->exercises->count();

        $attemptsCollection = collect($attempts)
            ->filter(fn($a) => (int) data_get($a, 'lesson_id') === (int) $lesson->id);

        $correct = $attemptsCollection->where('is_correct', true)->count();
        $attemptedExerciseIds = $attemptsCollection->pluck('exercise_id')->unique()->filter()->values();
        $attempted = $attemptedExerciseIds->count();

        $progress = $total > 0 ? (int) floor(($correct / $total) * 100) : 0;
        $finished = ($total > 0 && $attempted >= $total);
        $status = ($progress === 100) ? 'completado' : 'en_progreso';

        $progressRow = LessonUserProgress::firstOrNew([
            'user_id' => $userId,
            'lesson_id' => $lesson->id,
        ]);

        $progressRow->progress = $progress;
        $progressRow->status = $status;
        $progressRow->attempts_count = (int) ($progressRow->attempts_count ?? 0) + 1;
        if ($status === 'completado') {
            $progressRow->last_completed_at = now();
        }
        $progressRow->save();

        return ['progress' => $progress, 'status' => $status, 'finished' => $finished];
    }
}
