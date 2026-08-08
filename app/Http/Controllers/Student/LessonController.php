<?php

namespace App\Http\Controllers\Student;

use App\Classes\CollectionHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Unit;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index(Unit $unit)
    {
        $units = Unit::all();
        $unit->load([
            'lessons.lessonUserProgress' => fn ($q) => $q->where('user_id', auth()->id()),
            'lessons.unit:id,slug',
        ]);
        $lessons = CollectionHelper::transform($unit->lessons, function ($lesson) {
            return [
                'id' => $lesson->id,
                'slug' => $lesson->slug,
                'unit_id' => $lesson->unit_id,
                'unit_slug' => optional($lesson->unit)->slug,
                'name' => $lesson->name,
                'duration' => (int) $lesson->duration,
                'description' => $lesson->description,
                'image_url' => $lesson->image_url,
                'progress' => optional($lesson->lessonUserProgress->first())->progress ?? 0,
                'status' => optional($lesson->lessonUserProgress->first())->status ?? 'no_comenzado',
            ];
        });

        return Inertia::render('Student/Lessons/Index', [
            'units' => $units,
            'unit' => $unit,
            'lessons' => $lessons,
            'selectedUnit' => $unit->slug,
        ]);
    }

    public function show(Unit $unit, Lesson $lesson)
    {
        if ($lesson->unit_id !== $unit->id) {
            abort(404);
        }
        $lesson->load(['exercises.exerciseType', 'unit.level']);
        $lessonData = [
            'id' => $lesson->id,
            'name' => $lesson->name,
            'description' => $lesson->description,
            'duration' => (int) $lesson->duration,
            'unit' => $lesson->unit,
            'difficulty' => optional($lesson->unit->level)->name,
        ];
        $exercises = CollectionHelper::transform(collect($lesson->exercises), function ($exercise) {
            return [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'description' => $exercise->description,
                'prompt' => $exercise->prompt,
                'type' => $exercise->type,
                'exercise_type' => $exercise->exerciseType,
                'options' => is_array($exercise->options) ? $exercise->options : json_decode($exercise->options, true),
            ];
        });

        return Inertia::render('Student/Lessons/Show', [
            'lesson' => $lessonData,
            'exercises' => $exercises,
        ]);
    }
}
