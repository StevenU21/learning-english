<?php

namespace App\Services;

use App\DTOs\AddLessonActivityDTO;
use App\Models\LessonActivity;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    public function addLessonActivity(AddLessonActivityDTO $dto): void
    {
        $user = $dto->user;
        $lesson = $dto->lesson;
        $profile = $user->profile;
        if (! $profile) {
            return;
        }

        $duration = (int) ($lesson->duration ?? 0);
        if ($duration > 0) {
            $profile->increment('total_minutes', $duration);

            LessonActivity::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'minutes' => $duration,
            ]);
        }

        $today = now()->toDateString();
        DB::table('profile_streaks')->insertOrIgnore([
            'profile_id' => $profile->id,
            'activity_date' => $today,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
