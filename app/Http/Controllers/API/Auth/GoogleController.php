<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\FileService;

class GoogleController extends Controller
{
    /**
     * Handle Google OAuth for mobile clients using a Google access token.
     */
    public function authenticate(Request $request, FileService $fileService)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Verificar id_token con Google Tokeninfo API
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $request->token]);
        if ($response->failed()) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }
        $data = $response->json();
        $email = $data['email'] ?? null;
        $name = $data['name'] ?? '';
        $avatarUrl = $data['picture'] ?? null;
        $googleId = $data['sub'] ?? null;

        // Separar nombre y apellido
        $firstName = $name;
        $lastName = '';
        if (strpos($name, ' ') !== false) {
            [$firstName, $lastName] = preg_split('/\s+/', trim($name), 2);
        }

        // Encontrar o crear usuario
        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'first_name'        => $firstName ?: 'Usuario',
                'last_name'         => $lastName ?: 'Google',
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)),
                'google_id'         => $googleId,
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

        // Guardar avatar remoto si existe
        if ($avatarUrl) {
            $profile = $user->profile()->firstOrCreate([]);
            $saved = $fileService->storeRemote($profile, 'avatar', $avatarUrl);
            if ($saved) {
                $profile->avatar = $saved;
                $profile->save();
            }
        }

        // Generar token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 200);
    }
}
