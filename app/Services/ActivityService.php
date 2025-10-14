<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\ProfileStreak;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    /**
     * Add lesson activity time to the user's profile and ensure a streak entry for today.
     * - Increments profile.total_minutes by lesson duration (if > 0)
     * - Upserts today's row in profile_streaks (insert ignore semantics)
     */
    public function addLessonActivity(User $user, Lesson $lesson): void
    {
        $profile = $user->profile;
        if (!$profile) {
            return;
        }

        $duration = (int) ($lesson->duration ?? 0);
        if ($duration > 0) {
            $profile->increment('total_minutes', $duration);

            // Log an activity entry so daily goal can sum repeated finishes
            LessonActivity::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'minutes' => $duration,
            ]);
        }

        // Ensure today's streak entry exists
        $today = now()->toDateString();
        DB::table('profile_streaks')->insertOrIgnore([
            'profile_id' => $profile->id,
            'activity_date' => $today,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
