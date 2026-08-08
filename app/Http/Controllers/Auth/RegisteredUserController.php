<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            // Mensajes personalizados en español
            [
                'first_name.required' => 'El nombre es obligatorio.',
                'first_name.string' => 'El nombre debe ser una cadena de texto.',
                'first_name.max' => 'El nombre no debe exceder de :max caracteres.',
                'last_name.required' => 'El apellido es obligatorio.',
                'last_name.string' => 'El apellido debe ser una cadena de texto.',
                'last_name.max' => 'El apellido no debe exceder de :max caracteres.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no es válido.',
                'email.max' => 'El correo electrónico no debe exceder de :max caracteres.',
                'email.unique' => 'El correo electrónico ya está en uso.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            ],
            // Nombres de atributos en español
            [
                'first_name' => 'nombre',
                'last_name' => 'apellido',
                'email' => 'correo electrónico',
                'password' => 'contraseña',
            ]
        );

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Crear perfil asociado
        $user->profile()->create();

        $user->assignRole('student');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('student.units.index');
    }
}
