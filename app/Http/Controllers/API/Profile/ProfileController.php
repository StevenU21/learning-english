<?php

namespace App\Http\Controllers\API\Profile;

use App\DTOs\LocalFileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\ProfileResource;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get authenticated user's profile and stats.
     */
    public function show(Request $request): ProfileResource
    {
        return new ProfileResource($request->user());
    }

    /**
     * Update user's basic info (first name, last name, email).
     */
    public function updateUser(UserUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $originalEmail = $user->email;
        $user->fill($request->only(['first_name', 'last_name', 'email']));
        if ($user->isDirty('email') && $user->email !== $originalEmail) {
            $user->email_verified_at = null;
        }
        $user->save();

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user,
        ], 200);
    }

    /**
     * Update user's extended profile (nickname, birthdate, daily goal, gender, avatar).
     */
    public function updateProfile(ProfileUpdateRequest $request, FileService $fileService): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrNew([]);
        $data = $request->only(['nickname', 'birthdate', 'daily_goal_minutes', 'gender']);
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
            $stored = $fileService->updateLocal(new LocalFileDTO($profile, 'avatar', $request->file('avatar')));
            if (is_string($stored) && $stored !== '') {
                $profile->avatar = $stored;
            }
        }

        $profile->user()->associate($user);
        $profile->save();

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'profile' => new ProfileResource($user),
        ], 200);
    }

    /**
     * Delete authenticated user's account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        // Revoke all tokens
        $user->tokens()->delete();
        // Delete user
        $user->delete();

        return response()->json([
            'message' => 'Cuenta eliminada exitosamente',
        ], 200);
    }
}
