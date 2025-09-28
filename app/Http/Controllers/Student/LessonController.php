<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
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
}
