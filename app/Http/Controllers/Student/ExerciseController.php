<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonUserProgress;
use App\Models\Unit;
use App\Models\UnitUserProgress;
use App\Models\UserExerciseAttempt;
use App\Models\ProfileStreak;
use App\Http\Requests\UserExerciseAttemptRequest;
use Inertia\Inertia;
use App\Services\StreakService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    public function showSequence(Unit $unit, Lesson $lesson)
    {
        // Ensure the lesson belongs to the provided unit
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



        foreach ($attempts as $attempt) {
            // Calcular el siguiente número de intento para este usuario y ejercicio
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
        // Actualizar la racha del usuario tras los ejercicios del día (asegura la fila de hoy)
        $streakDays = (new StreakService())->updateStreak($request->user());

        $lessonId = $attempts[0]['lesson_id'] ?? null;
        $unitId = $attempts[0]['unit_id'] ?? null;

        // Guardar progreso de la lección
        if ($lessonId) {
            $lesson = Lesson::with('exercises')->find($lessonId);
            $total = $lesson->exercises->count();
            $correct = collect($attempts)->where('is_correct', true)->count();
            $progress = $total > 0 ? intval(($correct / $total) * 100) : 0;
            $status = $progress === 100 ? 'completado' : 'en_progreso';

            // Verificar estado previo para evitar doble conteo de minutos
            $existing = LessonUserProgress::where('user_id', $userId)
                ->where('lesson_id', $lessonId)
                ->first();
            $justCompleted = ($status === 'completado') && (!$existing || $existing->status !== 'completado');

            LessonUserProgress::updateOrCreate(
                [
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                ],
                [
                    'progress' => $progress,
                    'status' => $status,
                ]
            );



            // Si la lección acaba de completarse, sumar su duración a minutos del día y totales del perfil
            if ($justCompleted) {
                $duration = (int) ($lesson->duration ?? 0);
                $profile = $request->user()->profile;
                if ($profile && $duration > 0) {
                    // Incrementar minutos totales del perfil
                    $profile->increment('total_minutes', $duration);



                    // Asegurar fila de racha del día (sin minutos)
                    $today = now()->toDateString();
                    DB::table('profile_streaks')->insertOrIgnore([
                        'profile_id' => $profile->id,
                        'activity_date' => $today,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                }
            }

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
            // Find Unit model to leverage slug route binding on redirect
            $unit = Unit::find($unitId);
            return redirect()->route('student.units.lessons.index', $unit);
        }
        return redirect()->route('student.units.index');
    }
}
