<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExerciseTypeRequest;
use App\Models\ExerciseType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class ExerciseTypeController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', ExerciseType::class);
        $exerciseTypes = ExerciseType::paginate(10);
        return Inertia::render('Admin/ExerciseTypes/Index', [
            'exerciseTypes' => $exerciseTypes
        ]);
    }

    public function create()
    {
        $this->authorize('create', ExerciseType::class);
        return Inertia::render('Admin/ExerciseTypes/Create');
    }

    public function store(ExerciseTypeRequest $request)
    {
        ExerciseType::create($request->validated());
        return redirect()->route('exercise-types.index')->with('success', 'Tipo de ejercicio creado correctamente');
    }

    public function show(ExerciseType $exerciseType)
    {
        $this->authorize('view', $exerciseType);
        return Inertia::render('Admin/ExerciseTypes/Show', [
            'exerciseType' => $exerciseType
        ]);
    }

    public function edit(ExerciseType $exerciseType)
    {
        $this->authorize('update', $exerciseType);
        return Inertia::render('Admin/ExerciseTypes/Edit', [
            'exerciseType' => $exerciseType
        ]);
    }

    public function update(ExerciseTypeRequest $request, ExerciseType $exerciseType)
    {
        $exerciseType->update($request->validated());
        return redirect()->route('exercise-types.index')->with('success', 'Tipo de ejercicio actualizado correctamente');
    }

    public function destroy(ExerciseType $exerciseType)
    {
        $this->authorize('destroy', $exerciseType);
        $exerciseType->delete();
        return redirect()->route('exercise-types.index')->with('success', 'Tipo de ejercicio eliminado correctamente');
    }
}
