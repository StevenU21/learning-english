<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\FileService;
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
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->load(['profile']);
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
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
     * Update only the user's profile data (nickname, birthdate, academic_level, gender).
     */
    public function updateProfile(ProfileUpdateRequest $request, FileService $fileService): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $profile = $user->profile()->firstOrNew([]);
            $profile->fill($request->only([
                'nickname',
                'birthdate',
                'academic_level',
                'gender',
            ]));

            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                $profile->avatar = $fileService->updateLocal($profile, 'avatar', $request->file('avatar'));
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
