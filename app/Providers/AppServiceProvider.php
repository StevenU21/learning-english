<?php

namespace App\Providers;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Resource;
use App\Models\Unit;
use App\Policies\ExercisePolicy;
use App\Policies\ExerciseTypePolicy;
use App\Policies\LessonPolicy;
use App\Policies\LevelPolicy;
use App\Policies\ResourcePolicy;
use App\Policies\UnitPolicy;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Paginator::useTailwind();
        Gate::policy(Level::class, LevelPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Resource::class, ResourcePolicy::class);
        Gate::policy(Lesson::class, LessonPolicy::class);
        Gate::policy(Exercise::class, ExercisePolicy::class);
        Gate::policy(ExerciseType::class, ExerciseTypePolicy::class);
        // Define an HTTP macro for OpenAI realtime sessions
        Http::macro('openaiRealtime', function () {
            $apiKey = config('openai.api_key');
            if (empty($apiKey)) {
                abort(503, 'El servicio de IA no está configurado. Contacta al equipo de soporte.');
            }
            $client = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'realtime=v1',
            ])->timeout(config('openai.request_timeout', 30));
            if ($baseUrl = config('openai.base_uri')) {
                $client = $client->baseUrl($baseUrl);
            }
            return $client;
        });
    }
}
