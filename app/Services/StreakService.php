<?php

namespace App\Services;

use App\Models\ProfileStreak;
use App\Models\User;
use App\Models\UserExerciseAttempt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StreakService
{
    /**
     * Upsert today's streak row and return current streak days (consecutive up to today).
     */
    public function updateStreak(User $user): int
    {
        $profile = $user->profile;
        if (!$profile) {
            return 0;
        }

        $today = Carbon::today();

        // Any attempt today?
        $didToday = UserExerciseAttempt::where('user_id', $user->id)
            ->whereDate('answered_at', $today->toDateString())
            ->exists();

        if ($didToday) {
            // Aggregate minutes from today's attempts
            $attemptsToday = UserExerciseAttempt::where('user_id', $user->id)
                ->whereDate('answered_at', $today->toDateString())
                ->get(['started_at', 'answered_at']);

            $minutes = $attemptsToday->reduce(function (int $carry, $a) {
                $started = $a->started_at ? Carbon::parse($a->started_at) : Carbon::parse($a->answered_at);
                $answered = Carbon::parse($a->answered_at);
                // Guard against negatives
                $diff = max(0, $started->diffInMinutes($answered));
                return $carry + $diff;
            }, 0);

            // Upsert today's streak row
            ProfileStreak::updateOrCreate(
                [
                    'profile_id' => $profile->id,
                    'activity_date' => $today->toDateString(),
                ],
                [
                    'minutes' => $minutes,
                ]
            );
        }

        return $this->getCurrentStreak($user);
    }

    /**
     * Compute current streak (consecutive days including today if active).
     */
    public function getCurrentStreak(User $user): int
    {
        $profile = $user->profile;
        if (!$profile) {
            return 0;
        }

        $today = Carbon::today();

        // Load last 60 days of streaks for this profile
        $rows = ProfileStreak::where('profile_id', $profile->id)
            ->whereDate('activity_date', '<=', $today->toDateString())
            ->orderByDesc('activity_date')
            ->limit(60)
            ->get(['activity_date']);

        if ($rows->isEmpty()) {
            return 0;
        }

        // Create a set of activity dates for quick lookup
        $dates = $rows->map(fn ($r) => Carbon::parse($r->activity_date)->toDateString())->toArray();
        $dateSet = array_flip($dates);

        // Count consecutive days starting from today or yesterday if today inactive
        $cursor = in_array($today->toDateString(), $dates, true)
            ? $today->copy()
            : Carbon::parse($dates[0]); // start from most recent active day

        $streak = 0;
        while (isset($dateSet[$cursor->toDateString()])) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
