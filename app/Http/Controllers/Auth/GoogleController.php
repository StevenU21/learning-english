<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Services\FileService;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(FileService $fileService): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?: '';
        $avatarUrl = $googleUser->getAvatar();

        // Split name into first and last
        $firstName = $name;
        $lastName = '';
        if (strpos($name, ' ') !== false) {
            $parts = preg_split('/\s+/', trim($name), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'first_name' => $firstName ?: 'Usuario',
                'last_name' => $lastName ?: 'Google',
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);
            // Crear perfil asociado vacío
            $user->profile()->create();
            // Rol por defecto estudiante
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('student');
            }
        } else {
            // Asociar google_id si aún no está, y verificar email
            $updates = [];
            if (empty($user->google_id)) {
                $updates['google_id'] = $googleUser->getId();
            }
            if (empty($user->email_verified_at)) {
                $updates['email_verified_at'] = now();
            }
            if (!empty($updates)) {
                $user->fill($updates)->save();
            }
        }

        // Intentar guardar avatar remoto usando FileService si hay URL
        if ($avatarUrl) {
            $profile = $user->profile()->firstOrCreate([]);
            $saved = $fileService->storeRemote($profile, 'avatar', $avatarUrl);
            if ($saved) {
                $profile->avatar = $saved;
                $profile->save();
            }
        }

        Auth::login($user, remember: true);

        return redirect()->route('student.units.index');
    }
}
