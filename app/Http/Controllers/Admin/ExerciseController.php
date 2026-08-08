<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PermissionHelper;
use App\DTOs\ExerciseDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExerciseRequest;
use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\Lesson;
use App\Models\Unit;
use App\Services\ExerciseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ExerciseService $exerciseService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Exercise::class);
        $permissions = PermissionHelper::getPermissions('exercises');

        $type = request('type');
        $lesson = request('lesson');
        $unit = request('unit');

        $exercises = Exercise::query()
            ->when($type, fn ($q) => $q->where('exercise_type_id', $type))
            ->when($lesson, fn ($q) => $q->where('lesson_id', $lesson))
            ->when($unit, fn ($q) => $q->whereHas('lesson', fn ($q) => $q->where('unit_id', $unit)))
            ->with(['exerciseType', 'lesson'])
            ->latest()
            ->paginate(10)
            ->appends(request()->all());

        $typeIds = Exercise::query()
            ->when($lesson, fn ($q) => $q->where('lesson_id', $lesson))
            ->distinct()->pluck('exercise_type_id')->filter()->values();
        $types = ExerciseType::whereIn('id', $typeIds)->orderBy('name')->get(['id', 'name']);

        $lessonIds = Exercise::query()
            ->when($type, fn ($q) => $q->where('exercise_type_id', $type))
            ->distinct()->pluck('lesson_id')->filter()->values();
        $lessons = Lesson::whereIn('id', $lessonIds)->orderBy('name')->get(['id', 'name']);

        $allTypes = ExerciseType::orderBy('name')->get(['id', 'name']);
        $allLessons = Lesson::orderBy('name')->get(['id', 'name', 'unit_id']);
        $allUnits = Unit::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Exercises/Index', [
            'exercises' => $exercises,
            'permissions' => $permissions,
            'filters' => compact('type', 'lesson', 'unit'),
            'types' => $types,
            'lessons' => $lessons,
            'allTypes' => $allTypes,
            'allLessons' => $allLessons,
            'allUnits' => $allUnits,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Exercise::class);

        return Inertia::render('Admin/Exercises/Create', [
            'types' => ExerciseType::all(['id', 'name']),
            'lessons' => Lesson::orderBy('name')->get(['id', 'name', 'unit_id']),
            'units' => Unit::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ExerciseRequest $request): RedirectResponse
    {
        $this->authorize('create', Exercise::class);

        try {
            $dto = ExerciseDTO::fromRequest($request);
            $this->exerciseService->createExercise($dto);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Ocurrió un error al crear el ejercicio: '.$e->getMessage()])->withInput();
        }

        return redirect()->route('exercises.index', request()->query())->with('success', 'Ejercicio creado correctamente');
    }

    public function show(Exercise $exercise)
    {
        $this->authorize('view', $exercise);
        $exercise->load(['exerciseType', 'lesson']);
        $permissions = PermissionHelper::getPermissions('exercises');

        return Inertia::render('Admin/Exercises/Show', [
            'exercise' => $exercise,
            'permissions' => $permissions,
        ]);
    }

    public function edit(Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        return Inertia::render('Admin/Exercises/Edit', [
            'exercise' => $exercise,
            'types' => ExerciseType::all(['id', 'name']),
            'lessons' => Lesson::orderBy('name')->get(['id', 'name', 'unit_id']),
            'units' => Unit::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(ExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $exercise);

        try {
            $dto = ExerciseDTO::fromRequest($request);
            $this->exerciseService->updateExercise($exercise, $dto);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Ocurrió un error al actualizar el ejercicio.'])->withInput();
        }

        return redirect()->route('exercises.index', request()->query())->with('success', 'Ejercicio actualizado correctamente');
    }

    public function destroy(Exercise $exercise): RedirectResponse
    {
        $this->authorize('destroy', $exercise);
        $exercise->delete();

        return redirect()->route('exercises.index', request()->query())->with('success', 'Ejercicio eliminado correctamente');
    }
}
