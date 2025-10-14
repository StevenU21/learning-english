<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonUserProgress;

class LessonProgressService
{
    /**
     * Update lesson progress for a user based on the provided attempts.
     * - status 'completado' ONLY when all answers are correct (100%).
     * - progress is computed by correctness (correct/total).
     * - also returns 'finished' flag when the user attempted all exercises in this batch
     *   (i.e., terminó la secuencia), aunque no todas sean correctas.
     * Increments attempts_count per session and sets last_completed_at if completed.
     * Returns an array with ['progress' => int, 'status' => string, 'finished' => bool].
     */
    public function updateFromAttempts(int $userId, Lesson $lesson, array $attempts): array
    {
        // Ensure lesson has exercises loaded
        if (!$lesson->relationLoaded('exercises')) {
            $lesson->load('exercises');
        }

        $total = $lesson->exercises->count();

        // Compute correctness and coverage for this batch
        $correct = 0;
        $attemptedExerciseIds = [];
        foreach ($attempts as $a) {
            if ((int)($a['lesson_id'] ?? 0) === (int)$lesson->id) {
                if (($a['is_correct'] ?? false) === true) {
                    $correct++;
                }
                $attemptedExerciseIds[(int)($a['exercise_id'] ?? 0)] = true;
            }
        }

        $attempted = count($attemptedExerciseIds);
        $progress = $total > 0 ? (int) floor(($correct / $total) * 100) : 0; // correctness-based
        $finished = ($total > 0 && $attempted >= $total); // finished sequence regardless of correctness
        $status = ($progress === 100) ? 'completado' : 'en_progreso';

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

        return ['progress' => $progress, 'status' => $status, 'finished' => $finished];
    }
}
