<?php

namespace App\Services;

use App\DTOs\UserStreakDTO;
use App\Models\ProfileStreak;
use App\Models\UserExerciseAttempt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StreakService
{
    public function updateStreak(UserStreakDTO $dto): int
    {
        $user = $dto->user;
        $profile = $user->profile;
        if (! $profile) {
            return 0;
        }

        $today = Carbon::today();

        $didToday = UserExerciseAttempt::where('user_id', $user->id)
            ->whereDate('answered_at', $today->toDateString())
            ->exists();

        if ($didToday) {
            $key = [
                'profile_id' => $profile->id,
                'activity_date' => $today->toDateString(),
            ];
            $exists = ProfileStreak::where($key)->exists();
            if (! $exists) {
                DB::table('profile_streaks')->insertOrIgnore(array_merge($key, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        return $this->getCurrentStreak($dto);
    }

    public function getCurrentStreak(UserStreakDTO $dto): int
    {
        $user = $dto->user;
        $profile = $user->profile;
        if (! $profile) {
            return 0;
        }

        $today = Carbon::today();

        $rows = ProfileStreak::where('profile_id', $profile->id)
            ->whereDate('activity_date', '<=', $today->toDateString())
            ->orderByDesc('activity_date')
            ->limit(60)
            ->get(['activity_date']);

        if ($rows->isEmpty()) {
            return 0;
        }

        $dates = collect($rows)->pluck('activity_date')->map(function ($d) {
            return Carbon::parse($d)->toDateString();
        })->values();
        $dateSet = $dates->flip();

        $cursor = $dates->contains($today->toDateString())
            ? $today->copy()
            : Carbon::parse($dates->first());

        $streak = 0;
        while ($dateSet->has($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
