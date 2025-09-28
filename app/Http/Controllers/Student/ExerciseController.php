<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonUserProgress;
use App\Models\UnitUserProgress;
use App\Models\UserExerciseAttempt;
use App\Http\Requests\UserExerciseAttemptRequest;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    public function showSequence($lessonId)
    {
        $lesson = Lesson::with('exercises.exerciseType')->findOrFail($lessonId);

        $exercises = $lesson->exercises->map(function ($exercise) {
            $exerciseArr = $exercise->toArray();
            $exerciseArr['options'] = is_array($exercise->options) ? $exercise->options : json_decode($exercise->options, true);
            return $exerciseArr;
        });
        return Inertia::render('Student/Exercises/Sequence', [
            'lesson' => $lesson,
            'exercises' => $exercises
        ]);
    }

    public function storeAttemptsBatch(UserExerciseAttemptRequest $request)
    {
        $userId = $request->user()->id;
        $attempts = $request->input('attempts');

        foreach ($attempts as $attempt) {
            UserExerciseAttempt::updateOrCreate(
                [
                    'user_id' => $userId,
                    'exercise_id' => $attempt['exercise_id'],
                    'attempt_number' => $attempt['attempt_number'],
                ],
                [
                    'answer_given' => $attempt['answer_given'],
                    'is_correct' => $attempt['is_correct'],
                    'started_at' => $attempt['started_at'] ?? now(),
                    'answered_at' => now(),
                ]
            );
        }

        $lessonId = $attempts[0]['lesson_id'] ?? null;
        $unitId = $attempts[0]['unit_id'] ?? null;

        // Guardar progreso de la lección
        if ($lessonId) {
            $lesson = Lesson::with('exercises')->find($lessonId);
            $total = $lesson->exercises->count();
            $correct = collect($attempts)->where('is_correct', true)->count();
            $progress = $total > 0 ? intval(($correct / $total) * 100) : 0;
            $status = $progress === 100 ? 'completado' : 'en_progreso';

            \App\Models\LessonUserProgress::updateOrCreate(
                [
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                ],
                [
                    'progress' => $progress,
                    'status' => $status,
                ]
            );

            // Guardar progreso de la unidad
            if ($unitId) {
                $unitLessons = Lesson::where('unit_id', $unitId)->pluck('id');
                $userLessonProgress = LessonUserProgress::where('user_id', $userId)
                    ->whereIn('lesson_id', $unitLessons)
                    ->get();

                $totalLessons = count($unitLessons);
                $sumProgress = $userLessonProgress->sum('progress');
                $completedCount = $userLessonProgress->where('status', 'completado')->count();

                $unitProgress = $totalLessons > 0 ? intval($sumProgress / $totalLessons) : 0;
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

        if ($unitId) {
            return redirect()->route('student.units.start', $unitId);
        }
        return redirect()->route('student.units.index');
    }
}
