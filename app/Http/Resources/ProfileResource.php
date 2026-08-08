<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\LessonUserProgress;
use App\Models\Unit;
use App\Models\UnitUserProgress;
use App\Models\UserExerciseAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = $this->resource;
        $userId = $user->id;

        // Lessons stats
        $lessonsWorked = LessonUserProgress::where('user_id', $userId)->count();
        $lessonsCompleted = LessonUserProgress::where('user_id', $userId)
            ->where(fn ($q) => $q->where('progress', '>=', 100)->orWhere('status', 'completado'))
            ->count();
        $avgLessonProgress = (float) (LessonUserProgress::where('user_id', $userId)->avg('progress') ?? 0);
        $lessonsTotal = (int) Lesson::count();

        // Units stats
        $unitsWorked = UnitUserProgress::where('user_id', $userId)->count();
        $unitsCompleted = UnitUserProgress::where('user_id', $userId)
            ->where(fn ($q) => $q->where('progress', '>=', 100)->orWhere('status', 'completado'))
            ->count();
        $avgUnitProgress = (float) (UnitUserProgress::where('user_id', $userId)->avg('progress') ?? 0);
        $unitsTotal = (int) Unit::count();

        // Exercises stats
        $totalAttempts = UserExerciseAttempt::where('user_id', $userId)->count();
        $correctAttempts = UserExerciseAttempt::where('user_id', $userId)->where('is_correct', true)->count();
        $accuracy = $totalAttempts > 0 ? round(($correctAttempts / $totalAttempts) * 100, 1) : 0.0;
        $lastActivity = UserExerciseAttempt::where('user_id', $userId)->max('answered_at');

        // Daily stats
        $today = now()->toDateString();
        $todayMinutes = (int) LessonActivity::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->sum('minutes');
        $dailyGoal = (int) ($user->profile->daily_goal_minutes ?? 0);
        $remainingToday = max(0, $dailyGoal - $todayMinutes);
        $dailyReached = $dailyGoal > 0 ? $todayMinutes >= $dailyGoal : false;

        $overallProgress = $avgLessonProgress > 0 ? $avgLessonProgress : $avgUnitProgress;

        return [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
            ],
            'profile' => $user->profile ? [
                'nickname' => $user->profile->nickname,
                'birthdate' => $user->profile->birthdate,
                'daily_goal_minutes' => $user->profile->daily_goal_minutes,
                'total_minutes' => $user->profile->total_minutes,
                'gender' => $user->profile->gender,
                'avatar_url' => $user->profile->avatar_url,
            ] : null,
            'stats' => [
                'overall' => [
                    'progress' => round($overallProgress, 1),
                    'last_activity' => optional($lastActivity)->toDateTimeString(),
                ],
                'daily' => [
                    'goal' => $dailyGoal,
                    'today' => $todayMinutes,
                    'remaining' => $remainingToday,
                    'reached' => $dailyReached,
                ],
                'units' => [
                    'total' => $unitsTotal,
                    'worked' => $unitsWorked,
                    'completed' => $unitsCompleted,
                    'avg_progress' => round($avgUnitProgress, 1),
                ],
                'lessons' => [
                    'total' => $lessonsTotal,
                    'worked' => $lessonsWorked,
                    'completed' => $lessonsCompleted,
                    'avg_progress' => round($avgLessonProgress, 1),
                ],
                'exercises' => [
                    'attempts' => $totalAttempts,
                    'correct' => $correctAttempts,
                    'accuracy' => $accuracy,
                ],
            ],
        ];
    }
}
