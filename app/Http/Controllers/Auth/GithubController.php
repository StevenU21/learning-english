<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Services\FileService;

class GithubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback(FileService $fileService): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        $email = $githubUser->getEmail();
        // GitHub puede no devolver email si es privado; genera uno temporal basado en id
        if (!$email) {
            $email = sprintf('%s@users.noreply.github.com', $githubUser->getId());
        }
        $name = $githubUser->getName() ?: ($githubUser->getNickname() ?: '');
        $avatarUrl = $githubUser->getAvatar();
        $githubId = $githubUser->getId();
        $githubToken = $githubUser->token ?? null;

        $firstName = $name;
        $lastName = '';
        if (strpos($name, ' ') !== false) {
            $parts = preg_split('/\s+/', trim($name), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $data = [
                'first_name' => $firstName ?: 'Usuario',
                'last_name' => $lastName ?: 'Github',
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ];
            if (Schema::hasColumn('users', 'github_id')) {
                $data['github_id'] = $githubId;
            }
            if (Schema::hasColumn('users', 'github_token')) {
                $data['github_token'] = $githubToken;
            }
            $user = User::create($data);
            // Crear perfil asociado vacío
            $user->profile()->create();
            // Rol por defecto estudiante
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('student');
            }
        } else {
            // Asociar github_id/token si las columnas existen y aún no están, y verificar email
            $updates = [];
            if (Schema::hasColumn('users', 'github_id') && empty($user->github_id)) {
                $updates['github_id'] = $githubId;
            }
            // Actualizar token si cambia o no existe
            if (Schema::hasColumn('users', 'github_token') && (empty($user->github_token) || $user->github_token !== $githubToken)) {
                $updates['github_token'] = $githubToken;
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
