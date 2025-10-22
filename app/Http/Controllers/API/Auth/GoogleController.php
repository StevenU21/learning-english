<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\FileService;

class GoogleController extends Controller
{

    public function authenticate(Request $request, FileService $fileService)
    {
        $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        try {
            $googleUser = Socialite::driver('google')->userFromToken($request->token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? '';
        $avatarUrl = $googleUser->getAvatar();
        $googleId = $googleUser->getId();

        $nameParts = collect(explode(' ', trim($name)));
        $firstName = $nameParts->first() ?: 'Usuario';
        $lastName = $nameParts->count() > 1 ? $nameParts->slice(1)->implode(' ') : 'Google';

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'first_name' => $firstName ?: 'Usuario',
                'last_name' => $lastName ?: 'Google',
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'google_id' => $googleId,
                'email_verified_at' => now(),
            ]);
            $user->profile()->create();
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('student');
            }
        } else {
            $updates = [];
            if (empty($user->google_id)) {
                $updates['google_id'] = $googleId;
            }
            if (empty($user->email_verified_at)) {
                $updates['email_verified_at'] = now();
            }

            if ($updates) {
                $user->fill($updates)->save();
            }
        }

        if ($avatarUrl) {
            $profile = $user->profile()->firstOrCreate([]);
            $saved = $fileService->storeRemote($profile, 'avatar', $avatarUrl);
            if ($saved) {
                $profile->avatar = $saved;
                $profile->save();
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
}
