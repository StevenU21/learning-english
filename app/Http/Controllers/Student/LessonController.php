<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index(Request $request, $id = null)
    {
        // Permitir obtener el id de unidad desde la ruta o desde query param
        $unitId = $id ?? $request->input('unit_id');
        $units = Unit::all();
        $unit = null;
        $lessons = collect();
        if ($unitId) {
            $unit = Unit::with([
                'lessons.lessonUserProgress' => function ($q) {
                    $q->where('user_id', auth()->id());
                }
            ])->findOrFail($unitId);
            $lessons = $unit->lessons->map(function ($lesson) {
                $progress = $lesson->lessonUserProgress->first();
                return [
                    'id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'name' => $lesson->name,
                    'description' => $lesson->description,
                    'image_url' => $lesson->image_url,
                    'progress' => $progress ? $progress->progress : 0,
                    'status' => $progress ? $progress->status : 'no_comenzado',
                ];
            });
        }

        return Inertia::render('Student/Lessons/Index', [
            'units' => $units,
            'unit' => $unit,
            'lessons' => $lessons,
            'selectedUnit' => $unitId
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
