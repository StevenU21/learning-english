<?php

namespace App\Http\Controllers\Student;

use App\Classes\CollectionHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\UserExerciseAttempt;
use App\Http\Requests\UserExerciseAttemptRequest;
use Inertia\Inertia;
use App\Services\StreakService;
use App\Services\ActivityService;
use App\Services\LessonProgressService;
use App\Services\UnitProgressService;
use App\Services\SayThePhraseService;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    public function showSequence(Unit $unit, Lesson $lesson)
    {
        if ($lesson->unit_id !== $unit->id) {
            abort(404);
        }
        $lesson->load(['exercises.exerciseType', 'unit']);

        $exercises = CollectionHelper::transform(collect($lesson->exercises), fn($exercise) => [
            ...$exercise->toArray(),
            'options' => is_array($exercise->options) ? $exercise->options : json_decode($exercise->options, true),
        ]);

        return Inertia::render('Student/Exercises/Sequence', [
            'lesson' => $lesson,
            'exercises' => $exercises
        ]);
    }

    public function storeAttemptsBatch(UserExerciseAttemptRequest $request, SayThePhraseService $sayThePhraseService)
    {
        $userId = $request->user()->id;
        $attempts = $request->input('attempts');

        return DB::transaction(function () use ($request, $userId, $attempts, $sayThePhraseService) {
            foreach ($attempts as &$attempt) {
                $attempt = $this->evaluateAttempt($attempt, $sayThePhraseService);
                $nextAttemptNumber = $this->getNextAttemptNumber($userId, $attempt['exercise_id']);
                $this->saveAttempt($userId, $attempt, $nextAttemptNumber);
            }
            (new StreakService())->updateStreak($request->user());
            $this->updateProgressAndActivities($request, $userId, $attempts);
            return $this->redirectAfterBatch($attempts);
        });
    }

    private function evaluateAttempt(array $attempt, SayThePhraseService $sayThePhraseService): array
    {
        if (($attempt['type_name'] ?? null) === 'Di la frase' && isset($attempt['audio_path'], $attempt['solution'])) {
            $evaluation = $sayThePhraseService->processAttempt([
                'audio_path' => $attempt['audio_path'],
                'solution' => $attempt['solution'],
                'language' => $attempt['language'] ?? 'en',
            ]);
            $attempt['answer_given'] = $evaluation['transcription'] ?? '';
            $attempt['is_correct'] = ($evaluation['score'] ?? 0) === 100;
            $attempt['score'] = $evaluation['score'] ?? null;
        }
        return $attempt;
    }

    private function getNextAttemptNumber($userId, $exerciseId): int
    {
        $lastAttempt = UserExerciseAttempt::where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->orderByDesc('attempt_number')
            ->first();
        return $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;
    }

    private function saveAttempt($userId, array $attempt, int $nextAttemptNumber): void
    {
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

    private function updateProgressAndActivities($request, $userId, $attempts): void
    {
        $lessonId = $attempts[0]['lesson_id'] ?? null;
        $unitId = $attempts[0]['unit_id'] ?? null;

        if ($lessonId) {
            $lesson = Lesson::with('exercises')->find($lessonId);
            $lp = app(LessonProgressService::class)->updateFromAttempts($userId, $lesson, $attempts);
            if (($lp['finished'] ?? false) === true) {
                app(ActivityService::class)->addLessonActivity($request->user(), $lesson);
            }
            if ($unitId) {
                app(UnitProgressService::class)->recalc($userId, (int) $unitId);
            }
        }
    }
    private function redirectAfterBatch($attempts)
    {
        $unitId = $attempts[0]['unit_id'] ?? null;
        if ($unitId) {
            $unit = Unit::find($unitId);
            return redirect()->route('student.units.lessons.index', $unit);
        }
        return redirect()->route('student.units.index');
    }
}
