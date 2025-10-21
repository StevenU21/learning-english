<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            // Si la petición espera JSON, responde con JSON, si no, redirige
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have the required authorization to perform this action.'], 403);
            }
            // Si hay página previa, vuelve atrás, si no, redirige según el rol
            $user = auth()->user();
            if ($user) {
                if ($user->hasRole('student')) {
                    return redirect()->intended(route('student.units.index'))->with('error', 'No tienes autorización para realizar esta acción.');
                }
                // Puedes agregar más roles aquí si lo necesitas
                return redirect()->intended(route('units.index'))->with('error', 'No tienes autorización para realizar esta acción.');
            }
            return redirect()->back()->with('error', 'No tienes autorización para realizar esta acción.');
        });
    })->create();
