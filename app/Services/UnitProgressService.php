<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonUserProgress;
use App\Models\UnitUserProgress;

class UnitProgressService
{
    public function recalc(int $userId, int $unitId): void
    {
        $lessonIds = Lesson::where('unit_id', $unitId)->pluck('id');
        $userLessonProgress = LessonUserProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->get();

        $totalLessons = count($lessonIds);
        $sumProgress = $userLessonProgress->sum('progress');
        $completedCount = $userLessonProgress->where('status', 'completado')->count();

        $unitProgress = $totalLessons > 0 ? (int) floor($sumProgress / $totalLessons) : 0;
        $unitStatus = $completedCount === $totalLessons && $totalLessons > 0
            ? 'completado'
            : ($sumProgress > 0 ? 'en_progreso' : 'no_comenzado');

        UnitUserProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'unit_id' => $unitId,
            ],
            [
                'progress' => $unitProgress,
                'status' => $unitStatus,
            ]
        );
    }
}
