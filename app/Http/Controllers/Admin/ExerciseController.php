<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExerciseRequest;
use App\Models\Lesson;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Classes\ExerciseTypeLogic;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Exercise::class);
        $permissions = PermissionHelper::getPermissions('exercises');

        $type = request('type') ?: null;      // id de tipo seleccionado
        $lesson = request('lesson') ?: null;  // id de lección seleccionada

        // Query principal (con ambos filtros aplicados si existen)
        $mainQuery = Exercise::query();
        if ($type) {
            $mainQuery->where('exercise_type_id', $type);
        }
        if ($lesson) {
            $mainQuery->where('lesson_id', $lesson);
        }

        // Paginamos resultados finales
        $exercises = $mainQuery->with('exerciseType', 'lesson')->paginate(10)->appends(request()->all());

        // Construimos la lista de tipos aplicando SOLO el filtro de lección (para poder cambiar de tipo)
        $typesQuery = Exercise::query();
        if ($lesson) {
            $typesQuery->where('lesson_id', $lesson);
        }
        $typeIds = $typesQuery->distinct()->pluck('exercise_type_id')->filter()->values();
        $types = ExerciseType::whereIn('id', $typeIds)->orderBy('name')->get(['id', 'name']);

        // Construimos la lista de lecciones aplicando SOLO el filtro de tipo (para poder cambiar de lección)
        $lessonsQuery = Exercise::query();
        if ($type) {
            $lessonsQuery->where('exercise_type_id', $type);
        }
        $lessonIds = $lessonsQuery->distinct()->pluck('lesson_id')->filter()->values();
        $lessons = Lesson::whereIn('id', $lessonIds)->orderBy('name')->get(['id', 'name']);

        // Provide both filtered lists (for filters UX) and full lists (for create/edit modals)
        $allTypes = ExerciseType::orderBy('name')->get(['id', 'name']);
        $allLessons = Lesson::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Exercises/Index', [
            'exercises' => $exercises,
            'permissions' => $permissions,
            'filters' => [
                'type' => $type,
                'lesson' => $lesson
            ],
            'types' => $types,
            'lessons' => $lessons,
            'allTypes' => $allTypes,
            'allLessons' => $allLessons,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Exercise::class);
        $types = ExerciseType::all(['id', 'name']);
        $lessons = Lesson::all(['id', 'name']);
        return Inertia::render('Admin/Exercises/Create', [
            'types' => $types,
            'lessons' => $lessons
        ]);
    }
    public function store(ExerciseRequest $request): RedirectResponse
    {
        $this->authorize('create', Exercise::class);
        $data = $request->validated();
        // Keep UploadedFile objects for type validation
        $uploadedA = $request->file('file');
        $uploadedB = $request->file('file_b');
        $data['file'] = $uploadedA;
        $data['file_b'] = $uploadedB;

        // Normalize arrays: drop null/empty strings and reindex
        $data['options'] = array_values(array_filter((array) ($data['options'] ?? []), function ($v) {
            return !is_null($v) && !(is_string($v) && trim($v) === '');
        }));
        $data['solution'] = array_values(array_filter((array) ($data['solution'] ?? []), function ($v) {
            return !is_null($v) && !(is_string($v) && trim($v) === '');
        }));
        $type = ExerciseType::find($data['exercise_type_id']);
        if (!$type instanceof ExerciseType) {
            return back()->withErrors(['exercise_type_id' => 'Tipo de ejercicio inválido.']);
        }

        $result = ExerciseTypeLogic::validateAndProcess($type->name, $data);
        if (!empty($result['errors'])) {
            return back()->withErrors($result['errors'])->withInput();
        }
        // If validation passed, persist files and replace with stored paths
        if ($uploadedA) {
            $storedA = $uploadedA->store('units', 'public');
            $result['data']['file'] = $storedA;
        }
        if ($uploadedB) {
            $storedB = $uploadedB->store('units', 'public');
            $result['data']['file_b'] = $storedB;
        }
        Exercise::create($result['data']);
        return redirect()->route('exercises.index')->with('success', 'Ejercicio creado correctamente');
    }

    public function show(Exercise $exercise)
    {
        $this->authorize('view', $exercise);
        $exercise->load('exerciseType', 'lesson');
        $permissions = PermissionHelper::getPermissions('exercises');
        return Inertia::render('Admin/Exercises/Show', [
            'exercise' => $exercise,
            'permissions' => $permissions,
        ]);
    }

    public function edit(Exercise $exercise)
    {
        $this->authorize('update', $exercise);
        $types = ExerciseType::all(['id', 'name']);
        $lessons = Lesson::all(['id', 'name']);
        return Inertia::render('Admin/Exercises/Edit', [
            'exercise' => $exercise,
            'types' => $types,
            'lessons' => $lessons
        ]);
    }

    public function update(ExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $exercise);
        $data = $request->validated();
        // Keep UploadedFile objects for type validation
        $uploadedA = $request->file('file');
        $uploadedB = $request->file('file_b');
        $data['file'] = $uploadedA;
        $data['file_b'] = $uploadedB;

        // Normalize arrays: drop null/empty strings and reindex
        $data['options'] = array_values(array_filter((array) ($data['options'] ?? []), function ($v) {
            return !is_null($v) && !(is_string($v) && trim($v) === '');
        }));
        $data['solution'] = array_values(array_filter((array) ($data['solution'] ?? []), function ($v) {
            return !is_null($v) && !(is_string($v) && trim($v) === '');
        }));
        $type = ExerciseType::find($data['exercise_type_id']);
        if (!$type instanceof ExerciseType) {
            return back()->withErrors(['exercise_type_id' => 'Tipo de ejercicio inválido.']);
        }

        $result = ExerciseTypeLogic::validateAndProcess($type->name, $data);
        if (!empty($result['errors'])) {
            return back()->withErrors($result['errors'])->withInput();
        }
        // Persist new files if any; delete old ones; keep existing paths otherwise
        if ($uploadedA) {
            if (!empty($exercise->file)) {
                Storage::disk('public')->delete($exercise->file);
            }
            $result['data']['file'] = $uploadedA->store('units', 'public');
        } else {
            unset($result['data']['file']);
        }
        if ($uploadedB) {
            if (!empty($exercise->file_b)) {
                Storage::disk('public')->delete($exercise->file_b);
            }
            $result['data']['file_b'] = $uploadedB->store('units', 'public');
        } else {
            unset($result['data']['file_b']);
        }
        $exercise->update($result['data']);
        return redirect()->route('exercises.index')->with('success', 'Ejercicio actualizado correctamente');
    }

    public function destroy(Exercise $exercise): RedirectResponse
    {
        $this->authorize('destroy', $exercise);
        $exercise->delete();
        return redirect()->route('exercises.index')->with('success', 'Ejercicio eliminado correctamente');
    }
}
