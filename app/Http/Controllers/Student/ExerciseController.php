<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonUserProgress;
use App\Models\Unit;
use App\Models\UnitUserProgress;
use App\Models\UserExerciseAttempt;
use App\Http\Requests\UserExerciseAttemptRequest;
use Inertia\Inertia;
use App\Services\StreakService;
use App\Services\ActivityService;
use App\Services\LessonProgressService;
use App\Services\UnitProgressService;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    public function showSequence(Unit $unit, Lesson $lesson)
    {
        if ($lesson->unit_id !== $unit->id) {
            abort(404);
        }
        $lesson->load(['exercises.exerciseType', 'unit']);

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

        return DB::transaction(function () use ($request, $userId, $attempts) {
            foreach ($attempts as $attempt) {
                $lastAttempt = UserExerciseAttempt::where('user_id', $userId)
                    ->where('exercise_id', $attempt['exercise_id'])
                    ->orderByDesc('attempt_number')
                    ->first();
                $nextAttemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

                UserExerciseAttempt::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'exercise_id' => $attempt['exercise_id'],
                        'attempt_number' => $nextAttemptNumber,
                    ],
                    [
                        'answer_given' => $attempt['answer_given'],
                        'is_correct' => $attempt['is_correct'],
                        'started_at' => $attempt['started_at'] ?? now(),
                        'answered_at' => now(),
                    ]
                );
            }
            $streakDays = (new StreakService())->updateStreak($request->user());

            $lessonId = $attempts[0]['lesson_id'] ?? null;
            $unitId = $attempts[0]['unit_id'] ?? null;

            if ($lessonId) {
                $lesson = Lesson::with('exercises')->find($lessonId);

                // Update lesson progress from attempts
                $lp = app(LessonProgressService::class)->updateFromAttempts($userId, $lesson, $attempts);

                // Add activity time and ensure today's streak via service ONLY when the lesson was finished in this batch
                if (($lp['finished'] ?? false) === true) {
                    app(ActivityService::class)->addLessonActivity($request->user(), $lesson);
                }

                // Recalculate unit progress
                if ($unitId) {
                    app(UnitProgressService::class)->recalc($userId, (int) $unitId);
                }
            }

            if ($unitId) {
                $unit = Unit::find($unitId);
                return redirect()->route('student.units.lessons.index', $unit);
            }
            return redirect()->route('student.units.index');
        });
    }
}
