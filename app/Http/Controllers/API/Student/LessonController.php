<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * List lessons, optionally filtered by unit id or slug.
     */
    public function index(Request $request, Unit $unit)
    {
        if (! $unit) {
            return response()->json(['error' => 'El parámetro unit es obligatorio.'], 422);
        }
        $lessons = Lesson::query()
            ->where('unit_id', $unit->id)
            ->with(['unit', 'lessonUserProgress' => fn ($q) => $q->where('user_id', auth()->id())])
            ->get();

        return LessonResource::collection($lessons);
    }

    /**
     * Show a specific lesson with progress, requires both unit and lesson.
     */
    public function show(Unit $unit, Lesson $lesson)
    {
        // Optionally validate that the lesson belongs to the unit
        if ($lesson->unit_id !== $unit->id) {
            return response()->json(['error' => 'La lección no pertenece a la unidad especificada.'], 404);
        }
        $lesson->load(['unit', 'lessonUserProgress' => fn ($q) => $q->where('user_id', auth()->id())]);

        return new LessonResource($lesson);
    }
}
