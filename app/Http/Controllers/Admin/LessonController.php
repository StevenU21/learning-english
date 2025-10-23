<?php

namespace App\Http\Controllers\Admin;

use App\Classes\CollectionHelper;
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

        $lessons = Lesson::with('unit')
            ->when($request->filled('unit'), fn($q) => $q->where('unit_id', $request->input('unit')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        CollectionHelper::transformPaginated($lessons, function ($lesson) {
            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'image_url' => $lesson->image_url,
                'description' => $lesson->description,
                'duration' => (int) $lesson->duration,
                'unit' => $lesson->unit,
            ];
        });

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
        return Inertia::render('Admin/Lessons/Show', [
            'lesson' => [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'image_url' => $lesson->image_url,
                'description' => $lesson->description,
                'duration' => (int) $lesson->duration,
                'unit' => $lesson->unit,
            ]
        ]);
    }

    public function create()
    {
        $this->authorize('create', Lesson::class);
        return Inertia::render('Admin/Lessons/Create', [
            'units' => Unit::all()
        ]);
    }

    public function store(LessonRequest $request)
    {
        $this->authorize('create', Lesson::class);
        Lesson::create($request->validated());
        return redirect()->route('lessons.index', $request->query())->with('success', 'Lección creada correctamente');
    }

    public function edit(Lesson $lesson)
    {
        $this->authorize('update', $lesson);
        return Inertia::render('Admin/Lessons/Edit', [
            'lesson' => $lesson,
            'units' => Unit::all()
        ]);
    }

    public function update(LessonRequest $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson);
        $lesson->update($request->validated());
        return redirect()->route('lessons.index', $request->query())->with('success', 'Lección actualizada correctamente');
    }

    public function destroy(Lesson $lesson)
    {
        $this->authorize('destroy', $lesson);
        $lesson->delete();
        return redirect()->route('lessons.index', request()->query())->with('success', 'Lección eliminada correctamente');
    }
}
