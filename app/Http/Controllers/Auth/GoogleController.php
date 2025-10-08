<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
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
            ]);
            // Crear perfil asociado vacío
            $user->profile()->create();
            // Rol por defecto estudiante
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('student');
            }
        }

        // Intentar guardar avatar en disco público si hay URL
        if ($avatarUrl) {
            try {
                $contents = @file_get_contents($avatarUrl);
                if ($contents !== false) {
                    $ext = pathinfo(parse_url($avatarUrl, PHP_URL_PATH) ?? 'avatar.jpg', PATHINFO_EXTENSION) ?: 'jpg';
                    $path = 'avatars/' . $user->id . '_' . time() . '.' . $ext;
                    Storage::disk('public')->put($path, $contents);
                    $profile = $user->profile()->firstOrCreate([]);
                    $profile->avatar = $path;
                    $profile->save();
                }
            } catch (\Throwable $e) {
                // ignore avatar failures
            }
        }

        Auth::login($user, remember: true);

        return redirect()->route('student.units.index');
    }
}
