<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index(Unit $unit)
    {
        $units = Unit::all();
        $unit->load([
            'lessons.lessonUserProgress' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ]);
        $lessons = $unit->lessons->map(function ($lesson) {
            $progress = $lesson->lessonUserProgress->first();
            return [
                'id' => $lesson->id,
                'slug' => $lesson->slug,
                'unit_id' => $lesson->unit_id,
                'name' => $lesson->name,
                'description' => $lesson->description,
                'image_url' => $lesson->image_url,
                'progress' => $progress ? $progress->progress : 0,
                'status' => $progress ? $progress->status : 'no_comenzado',
            ];
        });

        return Inertia::render('Student/Lessons/Index', [
            'units' => $units,
            'unit' => $unit,
            'lessons' => $lessons,
            'selectedUnit' => $unit->slug
        ]);
    }

    /**
     * Display the specified lesson summary with exercises.
     */
    public function show(Lesson $lesson)
    {
        $lesson->load(['exercises.exerciseType', 'unit']);
        $exercises = $lesson->exercises->map(function ($exercise) {
            $exerciseArr = $exercise->toArray();
            $exerciseArr['options'] = is_array($exercise->options) ? $exercise->options : json_decode($exercise->options, true);
            return $exerciseArr;
        });
        return Inertia::render('Student/Lessons/Show', [
            'lesson' => $lesson,
            'exercises' => $exercises,
        ]);
    }
}
