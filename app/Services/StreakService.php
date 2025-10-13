<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserExerciseAttempt;
use Carbon\Carbon;

class StreakService
{
    /**
     * Update the user's streak based on exercise attempts.
     *
     * @param User $user
     * @return int Current streak days
     */
    public function updateStreak(User $user): int
    {
        $profile = $user->profile;
        if (!$profile) {
            return 0;
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Ensure there's at least one attempt today
        $didToday = UserExerciseAttempt::where('user_id', $user->id)
            ->whereDate('answered_at', $today->toDateString())
            ->exists();

        if (!$didToday) {
            // No exercise today; do not modify streak
            return $profile->streak_days;
        }

        // Get the most recent attempt before today
        $lastAttempt = UserExerciseAttempt::where('user_id', $user->id)
            ->whereDate('answered_at', '<', $today->toDateString())
            ->orderByDesc('answered_at')
            ->first();

        if ($lastAttempt && $lastAttempt->answered_at->isSameDay($yesterday)) {
            // Continue the streak
            $profile->increment('streak_days');
        } else {
            // Reset streak to 1
            $profile->streak_days = 1;
            $profile->save();
        }

        return $profile->streak_days;
    }
}
