<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\FileService;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\LessonUserProgress;
use App\Models\UnitUserProgress;
use App\Models\UserExerciseAttempt;
use App\Models\LessonActivity;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show a read-only profile page for the current user.
     */
    public function index(Request $request): Response
    {
        $user = $request->user()->load(['profile']);

        // Compute progress stats
        $userId = $user->id;

        // Lessons stats
        $lessonsWorked = LessonUserProgress::where('user_id', $userId)->count();
        $lessonsCompleted = LessonUserProgress::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('progress', '>=', 100)->orWhere('status', 'completed');
            })
            ->count();
        $avgLessonProgress = (float) (LessonUserProgress::where('user_id', $userId)->avg('progress') ?? 0);
        $lessonsTotal = (int) Lesson::count();

        // Units stats
        $unitsWorked = UnitUserProgress::where('user_id', $userId)->count();
        $unitsCompleted = UnitUserProgress::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('progress', '>=', 100)->orWhere('status', 'completed');
            })
            ->count();
        $avgUnitProgress = (float) (UnitUserProgress::where('user_id', $userId)->avg('progress') ?? 0);
        $unitsTotal = (int) Unit::count();

        // Exercises attempts
        $totalAttempts = UserExerciseAttempt::where('user_id', $userId)->count();
        $correctAttempts = UserExerciseAttempt::where('user_id', $userId)->where('is_correct', true)->count();
        $accuracy = $totalAttempts > 0 ? round(($correctAttempts / $totalAttempts) * 100, 1) : 0.0;
        $lastActivity = UserExerciseAttempt::where('user_id', $userId)->max('answered_at');

        // Compute today's minutes from finished lesson activities today (sum of minutes), including repeated finishes
        $today = now()->toDateString();
        $todayMinutes = (int) LessonActivity::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->sum('minutes');
        $dailyGoal = (int) ($user->profile->daily_goal_minutes ?? 0);
        $remainingToday = max(0, $dailyGoal - $todayMinutes);
        $dailyReached = $dailyGoal > 0 ? $todayMinutes >= $dailyGoal : false;

        // Overall progress (use lesson avg as main indicator if available, else unit avg)
        $overallProgress = $avgLessonProgress > 0 ? $avgLessonProgress : $avgUnitProgress;

        return Inertia::render('Profile/Index', [
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
        ]);
    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->load(['profile']);
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
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
        ]);
    }

    /**
     * Update only the user's main data (first_name, last_name, email).
     */
    public function updateUser(UserUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $originalEmail = $user->email;
            $user->fill($request->only([
                'first_name',
                'last_name',
                'email',
            ]));
            if ($user->isDirty('email') && $user->email !== $originalEmail) {
                $user->email_verified_at = $user->email_verified_at instanceof \Carbon\Carbon ? null : $user->email_verified_at;
            }
            $user->save();
        }
        return Redirect::route('profile.edit');
    }

    /**
     * Update only the user's profile data (nickname, birthdate, daily_goal_minutes, total_minutes, streak_days, gender).
     */
    public function updateProfile(ProfileUpdateRequest $request, FileService $fileService): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $profile = $user->profile()->firstOrNew([]);
            // Normalize optional fields: convert empty strings to null to avoid DB unique/date issues
            $data = $request->only([
                'nickname',
                'birthdate',
                'daily_goal_minutes',
                'gender',
            ]);
            foreach (['nickname', 'birthdate', 'gender'] as $key) {
                if (array_key_exists($key, $data) && ($data[$key] === '' || $data[$key] === "\0")) {
                    $data[$key] = null;
                }
            }
            if (array_key_exists('daily_goal_minutes', $data) && ($data['daily_goal_minutes'] === '' || $data['daily_goal_minutes'] === null)) {
                $data['daily_goal_minutes'] = null;
            }
            $profile->fill($data);

            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                $stored = $fileService->updateLocal($profile, 'avatar', $request->file('avatar'));
                if (is_string($stored) && $stored !== '') {
                    $profile->avatar = $stored;
                }
            }

            $profile->user()->associate($user);
            $profile->save();
        }
        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
