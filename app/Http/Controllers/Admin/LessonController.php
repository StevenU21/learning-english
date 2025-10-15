<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LessonRequest;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class LessonController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Lesson::class);
        PermissionHelper::getPermissions('lessons');
        // Consulta base con posible filtro por unidad
        $query = Lesson::with('unit');
        if ($request->filled('unit')) {
            $query->where('unit_id', $request->input('unit'));
        }
        $lessons = $query->paginate(10)->withQueryString();

        $lessons->getCollection()->transform(function ($lesson) {
            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'duration' => (int) $lesson->duration,
                'description' => $lesson->description,
                'image_url' => $lesson->image_url,
                'unit' => $lesson->unit
            ];
        });

        // Lista de unidades para el filtro
        $units = Unit::all();
        return Inertia::render('Admin/Lessons/Index', [
            'lessons' => $lessons,
            'units' => $units,
            'filters' => [
                'unit' => $request->input('unit', ''),
            ],
        ]);
    }

    public function show(Lesson $lesson)
    {
        $this->authorize('view', $lesson);
        $lesson->load('unit');
        $lessonData = [
            'id' => $lesson->id,
            'name' => $lesson->name,
            'duration' => (int) $lesson->duration,
            'description' => $lesson->description,
            'image_url' => $lesson->image_url,
            'unit' => $lesson->unit,
            'created_at' => $lesson->created_at,
        ];
        return Inertia::render('Admin/Lessons/Show', [
            'lesson' => $lessonData
        ]);
    }

    public function create()
    {
        $this->authorize('create', Lesson::class);
        $units = Unit::all();
        return Inertia::render('Admin/Lessons/Create', [
            'units' => $units
        ]);
    }

    public function store(LessonRequest $request)
    {
        $this->authorize('create', Lesson::class);
        $data = $request->validated();
        Lesson::create($data);
        return redirect()->route('lessons.index')->with('success', 'Lección creada correctamente');
    }

    public function edit(Lesson $lesson)
    {
        $this->authorize('update', $lesson);
        $units = Unit::all();
        return Inertia::render('Admin/Lessons/Edit', [
            'lesson' => $lesson,
            'units' => $units
        ]);
    }

    public function update(LessonRequest $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson);
        $data = $request->validated();
        $lesson->update($data);
        return redirect()->route('lessons.index')->with('success', 'Lección actualizada correctamente');
    }

    public function destroy(Lesson $lesson)
    {
        $this->authorize('destroy', $lesson);
        $lesson->delete();
        return redirect()->route('lessons.index')->with('success', 'Lección eliminada correctamente');
    }
}
