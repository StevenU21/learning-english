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
        ];
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
