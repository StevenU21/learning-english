<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\UnitUserProgress;
use App\Models\User;
use App\Models\LessonUserProgress;
use App\Models\UserExerciseAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $unitId = $request->input('unit_id');
        $userId = $request->input('user_id');
        $lessonId = $request->input('lesson_id');
        $status = $request->input('status');

        $units = Unit::all();
        $users = User::all();
        $lessons = Lesson::all(['id', 'name', 'unit_id']);

        $progress = LessonUserProgress::with(['user.profile', 'lesson.unit'])
            ->when($unitId, fn($q) => $q->whereIn('lesson_id', Lesson::where('unit_id', $unitId)->pluck('id')))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($lessonId, fn($q) => $q->where('lesson_id', $lessonId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->appends($request->all());

        $progress->getCollection()->transform(fn($item) => tap($item, fn($i) => $i->avatar_url = optional($i->user->profile)->avatar_url));

        return Inertia::render('Admin/Progress/Index', [
            'units' => $units,
            'users' => $users,
            'lessons' => $lessons,
            'progress' => $progress,
            'selectedUnit' => $unitId,
            'selectedUser' => $userId,
            'selectedLesson' => $lessonId,
            'selectedStatus' => $status
        ]);
    }

    public function show($userId)
    {
        $user = User::with('profile')->findOrFail($userId);
        $units = Unit::all();
        $lessons = Lesson::with('unit')->get();
        $lessonProgress = LessonUserProgress::with(['lesson.unit'])
            ->where('user_id', $userId)
            ->get();
        $unitProgress = UnitUserProgress::with(['unit'])
            ->where('user_id', $userId)
            ->get();
        $attempts = UserExerciseAttempt::with(['lesson.unit', 'exercise.lesson.unit'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/Progress/Show', [
            'user' => array_merge($user->toArray(), [
                'avatar_url' => optional($user->profile)->avatar_url,
            ]),
            'units' => $units,
            'lessons' => $lessons,
            'lessonProgress' => $lessonProgress,
            'unitProgress' => $unitProgress,
            'attempts' => $attempts
        ]);
    }

    public function report($userId)
    {
        $user = User::with('profile')->findOrFail($userId);
        $units = Unit::all();
        $lessons = Lesson::with('unit')->get();
        $lessonProgress = LessonUserProgress::with(['lesson.unit'])
            ->where('user_id', $userId)
            ->get();
        $unitProgress = UnitUserProgress::with(['unit'])
            ->where('user_id', $userId)
            ->get();
        $attempts = UserExerciseAttempt::with(['lesson.unit', 'exercise.lesson.unit'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        $data = [
            'user' => array_merge($user->toArray(), [
                'avatar_url' => optional($user->profile)->avatar_url,
            ]),
            'units' => $units,
            'lessons' => $lessons,
            'lessonProgress' => $lessonProgress,
            'unitProgress' => $unitProgress,
            'attempts' => $attempts
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.student_progress', $data);
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="reporte_progreso_estudiante_' . $user->id . '.pdf"'
        ]);
    }
}
