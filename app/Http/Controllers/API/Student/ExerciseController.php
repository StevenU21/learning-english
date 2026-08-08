<?php

namespace App\Http\Controllers\API\Student;

use App\DTOs\AddLessonActivityDTO;
use App\DTOs\RecalcUnitProgressDTO;
use App\DTOs\UpdateLessonProgressDTO;
use App\DTOs\UserStreakDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserExerciseAttemptRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Lesson;
use App\Models\UserExerciseAttempt;
use App\Services\ActivityService;
use App\Services\LessonProgressService;
use App\Services\StreakService;
use App\Services\UnitProgressService;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    /**
     * List exercises for a given lesson.
     */
    public function index(Lesson $lesson)
    {
        $lesson->load(['exercises.exerciseType']);
        $exercises = $lesson->exercises;

        return ExerciseResource::collection($exercises);
    }

    /**
     * Store batch of user exercise attempts and update progress.
     */
    public function store(UserExerciseAttemptRequest $request)
    {
        $userId = $request->user()->id;
        $attempts = $request->input('attempts');

        $result = DB::transaction(function () use ($request, $userId, $attempts) {
            foreach ($attempts as $att) {
                $last = UserExerciseAttempt::where('user_id', $userId)
                    ->where('exercise_id', $att['exercise_id'])
                    ->orderByDesc('attempt_number')
                    ->first();
                $next = $last ? $last->attempt_number + 1 : 1;

                UserExerciseAttempt::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'exercise_id' => $att['exercise_id'],
                        'attempt_number' => $next,
                    ],
                    [
                        'answer_given' => $att['answer_given'],
                        'is_correct' => $att['is_correct'],
                        'started_at' => $att['started_at'] ?? now(),
                        'answered_at' => now(),
                    ]
                );
            }

            (new StreakService)->updateStreak(new UserStreakDTO($request->user()));

            $lessonId = $attempts[0]['lesson_id'] ?? null;
            $unitId = $attempts[0]['unit_id'] ?? null;
            $lessonProgress = null;

            if ($lessonId) {
                $lessonModel = Lesson::with('exercises')->find($lessonId);
                $lessonProgress = app(LessonProgressService::class)
                    ->updateFromAttempts(new UpdateLessonProgressDTO($userId, $lessonModel, $attempts));

                if (($lessonProgress['finished'] ?? false) === true) {
                    app(ActivityService::class)->addLessonActivity(new AddLessonActivityDTO($request->user(), $lessonModel));
                }
            }

            if ($unitId) {
                app(UnitProgressService::class)->recalc(new RecalcUnitProgressDTO($userId, (int) $unitId));
            }

            return ['lesson_progress' => $lessonProgress];
        });

        return response()->json([
            'message' => 'Intentos guardados',
            'data' => $result,
        ], 200);
    }
}
