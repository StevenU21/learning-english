<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Http\Resources\LessonResource;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * List lessons, optionally filtered by unit id or slug.
     */
    public function index(Request $request)
    {
        $unitParam = $request->input('unit') ?? $request->input('unit_id');
        $lessons = Lesson::query()
            ->when($unitParam, function ($q) use ($unitParam) {
                if (is_numeric($unitParam)) {
                    $q->where('unit_id', $unitParam);
                } else {
                    $q->whereHas('unit', fn($q) => $q->where('slug', $unitParam));
                }
            })
            ->with(['unit', 'lessonUserProgress' => fn($q) => $q->where('user_id', auth()->id())])
            ->get();

        return LessonResource::collection($lessons);
    }

    /**
     * Show a specific lesson with progress.
     */
    public function show(Lesson $lesson)
    {
        $lesson->load(['unit', 'lessonUserProgress' => fn($q) => $q->where('user_id', auth()->id())]);

        return new LessonResource($lesson);
    }
}
