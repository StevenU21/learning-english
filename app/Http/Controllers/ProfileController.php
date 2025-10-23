<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\FileService;
use App\Services\ProfileStatsService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()->load(['profile']);
        $stats = app(ProfileStatsService::class)->getStatsForUser($user);
        return Inertia::render('Profile/Index', $stats);
    }

    public function edit(Request $request): Response
    {
        $user = $request->user()->load(['profile']);
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $user->toArray(),
            'profile' => $user->profile ? $user->profile->toArray() : null,
        ]);
    }

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

    public function updateProfile(ProfileUpdateRequest $request, FileService $fileService): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $profile = $user->profile()->firstOrNew([]);
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
