<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonUserProgress;

class LessonProgressService
{
    /**
     * Update lesson progress for a user based on the provided attempts.
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

        // Count correct answers for this lesson from the batch
        $correct = 0;
        foreach ($attempts as $a) {
            if ((int)($a['lesson_id'] ?? 0) === (int)$lesson->id && ($a['is_correct'] ?? false)) {
                $correct++;
            }
        }

        $progress = $total > 0 ? (int) floor(($correct / $total) * 100) : 0;
        $status = $progress === 100 ? 'completado' : 'en_progreso';

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
