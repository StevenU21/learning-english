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
        // Filtros opcionales
        $unitId = $request->input('unit_id');
        $userId = $request->input('user_id');
        $lessonId = $request->input('lesson_id');
        $status = $request->input('status');

        $units = Unit::all();
        $users = User::all();
        $lessons = Lesson::all(['id', 'name', 'unit_id']);

        // Filtrar por unidad, usuario, lección y estado en LessonUserProgress
        $query = LessonUserProgress::with(['user.profile', 'lesson.unit']);
        if ($unitId) {
            $lessonIds = Lesson::where('unit_id', $unitId)->pluck('id');
            $query->whereIn('lesson_id', $lessonIds);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($lessonId) {
            $query->where('lesson_id', $lessonId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        // Paginate progress 10 per page with filters applied
        $progress = $query->paginate(10)->appends($request->all());
        // Añadir avatar_url al usuario en cada progreso
        $progress->getCollection()->transform(function ($item) {
            // Añadir avatar_url directamente al ítem para facilitar DataTable
            $item->avatar_url = optional($item->user->profile)->avatar_url;
            return $item;
        });

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
        // Cargar relación profile para obtener avatar
        $user = User::with('profile')->findOrFail($userId);
        $units = Unit::all();
        $lessons = Lesson::with('unit')->get();
        $lessonProgress = LessonUserProgress::with(['lesson.unit'])
            ->where('user_id', $userId)
            ->get();
        $unitProgress = UnitUserProgress::with(['unit'])
            ->where('user_id', $userId)
            ->get();
        // Cargar relaciones anidadas para poder filtrar por unidad en el front
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
}
