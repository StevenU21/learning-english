<?php

namespace App\Http\Controllers\Student;

use App\Classes\CollectionHelper;
use App\Http\Controllers\Controller;

use App\Models\Unit;
use App\Models\Lesson;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index(Unit $unit)
    {
        $units = Unit::all();
        $unit->load([
            'lessons.lessonUserProgress' => fn($q) => $q->where('user_id', auth()->id()),
            'lessons.unit:id,slug'
        ]);
        $lessons = CollectionHelper::transform($unit->lessons, fn($lesson) => [
            ...$lesson->toArray(),
            'progress' => optional($lesson->lessonUserProgress->first())->progress ?? 0,
            'status' => optional($lesson->lessonUserProgress->first())->status ?? 'no_comenzado',
        ]);

        return Inertia::render('Student/Lessons/Index', [
            'units' => $units,
            'unit' => $unit,
            'lessons' => $lessons,
            'selectedUnit' => $unit->slug
        ]);
    }

    public function show(Unit $unit, Lesson $lesson)
    {
        if ($lesson->unit_id !== $unit->id) {
            abort(404);
        }
        $lesson->load(['exercises.exerciseType', 'unit']);
        $exercises = CollectionHelper::transform(collect($lesson->exercises), fn($exercise) => [
            ...$exercise->toArray(),
            'options' => is_array($exercise->options) ? $exercise->options : json_decode($exercise->options, true),
        ]);
        return Inertia::render('Student/Lessons/Show', [
            'lesson' => $lesson,
            'exercises' => $exercises,
        ]);
    }
}
