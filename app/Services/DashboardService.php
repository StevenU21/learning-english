<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Exercise;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\UserExerciseAttempt;

class DashboardService
{
    public function getStats()
    {
        $counts = $this->getCounts();
        return [
            'students' => $counts['students'],
            'exercises' => $counts['exercises'],
            'units' => $counts['units'],
            'lessons' => $counts['lessons'],
            'total_attempts' => $counts['total_attempts'],
            'students_completed_all_units' => $this->getStudentsCompletedAllUnits($counts['students'], $counts['units']),
            'learning_hours' => $this->getLearningHours(),
            'global_accuracy' => $this->getGlobalAccuracy(),
            'top_students_hours' => $this->getTopStudentsByHours(),
            'top_students_lessons' => $this->getTopStudentsByLessons(),
            'top_exercises_errors' => $this->getTopExercisesByErrors(),
            'top_lessons_popular' => $this->getTopLessonsPopular(),
            'top_students_accuracy' => $this->getTopStudentsByAccuracy(),
            'top_exercises_most_attempted' => $this->getTopExercisesMostAttempted(),
        ];
    }
    // Top 3 ejercicios más realizados (más intentos)
    public function getTopExercisesMostAttempted()
    {
        return DB::table('user_exercise_attempts')
            ->join('exercises', 'user_exercise_attempts.exercise_id', '=', 'exercises.id')
            ->join('exercise_types', 'exercises.exercise_type_id', '=', 'exercise_types.id')
            ->select('exercise_types.name as type', DB::raw('COUNT(*) as attempts'))
            ->groupBy('exercise_types.id', 'exercise_types.name')
            ->orderByDesc('attempts')
            ->limit(3)
            ->get()
            ->map(function ($row) {
                return ['type' => $row->type, 'attempts' => $row->attempts];
            })
            ->toArray();
    }

    // Top 5 estudiantes por horas de aprendizaje
    public function getTopStudentsByHours()
    {
        return DB::table('lesson_user_progress')
            ->where('status', 'completado')
            ->join('lessons', 'lesson_user_progress.lesson_id', '=', 'lessons.id')
            ->join('users', 'lesson_user_progress.user_id', '=', 'users.id')
            ->select('users.first_name', 'users.last_name', DB::raw('SUM(lessons.duration)/60 as hours'))
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('hours')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $short_last_name = explode(' ', $row->last_name)[0];
                return [
                    'name' => $row->first_name . ' ' . $short_last_name,
                    'hours' => round($row->hours, 1)
                ];
            })
            ->toArray();
    }

    // Top 5 estudiantes por lecciones completadas
    public function getTopStudentsByLessons()
    {
        return DB::table('lesson_user_progress')
            ->where('status', 'completado')
            ->join('users', 'lesson_user_progress.user_id', '=', 'users.id')
            ->select('users.first_name', 'users.last_name', DB::raw('COUNT(*) as lessons'))
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('lessons')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $short_last_name = explode(' ', $row->last_name)[0];
                return [
                    'name' => $row->first_name . ' ' . $short_last_name,
                    'lessons' => $row->lessons
                ];
            })
            ->toArray();
    }

    // Top 5 ejercicios con más errores
    public function getTopExercisesByErrors()
    {
        return DB::table('user_exercise_attempts')
            ->where('is_correct', false)
            ->join('exercises', 'user_exercise_attempts.exercise_id', '=', 'exercises.id')
            ->join('exercise_types', 'exercises.exercise_type_id', '=', 'exercise_types.id')
            ->select('exercise_types.name as type', DB::raw('COUNT(*) as errors'))
            ->groupBy('exercise_types.id', 'exercise_types.name')
            ->orderByDesc('errors')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return ['type' => $row->type, 'errors' => $row->errors];
            })
            ->toArray();
    }

    // Top lecciones más populares (más completadas)
    public function getTopLessonsPopular()
    {
        return DB::table('lesson_user_progress')
            ->where('status', 'completado')
            ->join('lessons', 'lesson_user_progress.lesson_id', '=', 'lessons.id')
            ->select('lessons.name', DB::raw('COUNT(*) as completions'))
            ->groupBy('lessons.id', 'lessons.name')
            ->orderByDesc('completions')
            ->limit(3)
            ->get()
            ->map(function ($row) {
                return ['lesson' => $row->name, 'completions' => $row->completions];
            })
            ->toArray();
    }

    // Top 3 estudiantes con mayor precisión
    public function getTopStudentsByAccuracy()
    {
        return DB::table('user_exercise_attempts')
            ->join('users', 'user_exercise_attempts.user_id', '=', 'users.id')
            ->select(
                'users.first_name',
                'users.last_name',
                DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                DB::raw('COUNT(*) as total'),
                DB::raw('ROUND(SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as accuracy')
            )
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->having('total', '>', 0)
            ->orderByDesc('accuracy')
            ->limit(3)
            ->get()
            ->map(function ($row) {
                $short_last_name = explode(' ', $row->last_name)[0];
                return [
                    'name' => $row->first_name . ' ' . $short_last_name,
                    'accuracy' => $row->accuracy
                ];
            })
            ->toArray();
    }

    public function getCounts()
    {
        return [
            'students' => User::role('student')->count(),
            'admins' => User::role('admin')->count(),
            'exercises' => Exercise::count(),
            'units' => Unit::count(),
            'lessons' => Lesson::count(),
            'total_attempts' => UserExerciseAttempt::count(),
        ];
    }

    public function getStudentsCompletedAllUnits($totalStudents, $totalUnits)
    {
        $studentsWithAllUnits = DB::table('unit_user_progress')
            ->where('status', 'completado')
            ->select('user_id', DB::raw('count(*) as completed_units'))
            ->groupBy('user_id')
            ->having('completed_units', '>=', $totalUnits)
            ->count();
        return $totalStudents > 0 ? round($studentsWithAllUnits * 100 / $totalStudents, 1) : 0;
    }

    public function getLearningHours()
    {
        $totalMinutes = DB::table('lesson_user_progress')
            ->where('status', 'completado')
            ->join('lessons', 'lesson_user_progress.lesson_id', '=', 'lessons.id')
            ->sum('lessons.duration');
        return round($totalMinutes / 60, 1);
    }

    public function getGlobalAccuracy()
    {
        $totalAttempts = UserExerciseAttempt::count();
        $totalCorrect = UserExerciseAttempt::where('is_correct', true)->count();
        return $totalAttempts > 0 ? round($totalCorrect * 100 / $totalAttempts, 1) : 0;
    }
}
