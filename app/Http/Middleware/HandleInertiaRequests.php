<?php

namespace App\Http\Middleware;

use App\DTOs\UserStreakDTO;
use App\Services\StreakService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user
                    ? array_merge(
                        $user->only(['id', 'first_name', 'last_name', 'email']),
                        [
                            'full_name' => $user->full_name,
                            'short_name' => $user->short_name,
                            // Profile image URL
                            'avatar_url' => optional($user->profile)->avatar_url,
                            // Array of role names
                            'roles' => $user->getRoleNames()->toArray(),
                            // Array of permission names
                            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                            // Streak days from service (computed)
                            'streak_days' => app(StreakService::class)->getCurrentStreak(new UserStreakDTO($user)),
                        ]
                    )
                    : null,
            ],
        ];
    }
}
