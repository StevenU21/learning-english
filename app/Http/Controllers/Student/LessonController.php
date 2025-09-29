<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Lesson;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index($unitId)
    {
        $unit = Unit::with([
            'lessons.lessonUserProgress' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ])->findOrFail($unitId);

        $lessons = $unit->lessons->map(function ($lesson) {
            $progress = $lesson->lessonUserProgress->first();
            $lessonArr = $lesson->toArray();
            $lessonArr['progress'] = $progress ? $progress->progress : 0;
            $lessonArr['status'] = $progress ? $progress->status : 'no_comenzado';
            return $lessonArr;
        });

        return Inertia::render('Student/Lessons/Index', [
            'unit' => $unit,
            'lessons' => $lessons
        ]);
    }

    /**
     * Display the specified lesson summary with exercises.
     */
    public function show($lessonId)
    {
        $lesson = Lesson::with('exercises.exerciseType')->findOrFail($lessonId);
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
