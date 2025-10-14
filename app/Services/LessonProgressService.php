<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonUserProgress;

class LessonProgressService
{
    /**
     * Update lesson progress for a user based on the provided attempts.
     * Criteria change: a lesson is considered "completado" when the user
     * has finished the sequence (attempted all exercises), regardless of correctness.
     * Progress is computed by coverage (attempted/total) instead of correctness.
     *
     * Increments attempts_count per session and sets last_completed_at if completed.
     * Returns an array with ['progress' => int, 'status' => string].
     */
    public function updateFromAttempts(int $userId, Lesson $lesson, array $attempts): array
    {
        // Ensure lesson has exercises loaded
        if (!$lesson->relationLoaded('exercises')) {
            $lesson->load('exercises');
        }

        $total = $lesson->exercises->count();

        // Determine coverage: how many distinct exercises from this lesson were attempted in this batch
        $attemptedExerciseIds = [];
        foreach ($attempts as $a) {
            if ((int) ($a['lesson_id'] ?? 0) === (int) $lesson->id) {
                $attemptedExerciseIds[(int) ($a['exercise_id'] ?? 0)] = true;
            }
        }

        $attempted = count($attemptedExerciseIds);
        $progress = $total > 0 ? (int) floor(($attempted / $total) * 100) : 0;
        $status = ($total > 0 && $attempted >= $total) ? 'completado' : 'en_progreso';

        $progressRow = LessonUserProgress::firstOrNew([
            'user_id' => $userId,
            'lesson_id' => $lesson->id,
        ]);

        $progressRow->progress = $progress;
        $progressRow->status = $status;
        $progressRow->attempts_count = (int) ($progressRow->attempts_count ?? 0) + 1; // count every session
        if ($status === 'completado') {
            $progressRow->last_completed_at = now();
        }
        $progressRow->save();

        return ['progress' => $progress, 'status' => $status];
    }
}
