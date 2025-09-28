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
        Gate::policy(Level::class, LevelPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Resource::class, ResourcePolicy::class);
        Gate::policy(Lesson::class, LessonPolicy::class);
        Gate::policy(Exercise::class, ExercisePolicy::class);
        Gate::policy(ExerciseType::class, ExerciseTypePolicy::class);
    }
}
