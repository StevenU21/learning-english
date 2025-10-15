<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Level;
use App\Models\Unit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class UnitController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', Unit::class);

        $units = Unit::with('level')
            ->withSum('lessons as lessons_sum_duration', 'duration')
            ->paginate(10);

        $units->getCollection()->transform(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'description' => $unit->description,
                'expected_time' => (int) $unit->expected_time,
                'image_url' => $unit->image_url,
                'level' => $unit->level
            ];
        });

        // Also provide levels for create/edit modals rendered inside Index
        $levels = Level::all();

        return Inertia::render('Admin/Units/Index', [
            'units' => $units,
            'levels' => $levels,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Unit::class);
        $levels = Level::all();
        return Inertia::render('Admin/Units/Create', [
            'levels' => $levels
        ]);
    }

    public function store(UnitRequest $request)
    {
        $this->authorize('create', Unit::class);
        Unit::create($request->validated());

        return redirect()->route('units.index')->with('success', 'Unidad creada correctamente');
    }

    public function show(Unit $unit)
    {
        $this->authorize('view', $unit);
        $unit->load('level')
            ->loadSum('lessons as lessons_sum_duration', 'duration');
        $unitData = [
            'id' => $unit->id,
            'name' => $unit->name,
            'description' => $unit->description,
            'expected_time' => (int) $unit->expected_time,
            'image_url' => $unit->image_url,
            'level' => $unit->level,
            'created_at' => $unit->created_at,
        ];
        return Inertia::render('Admin/Units/Show', [
            'unit' => $unitData
        ]);
    }

    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);
        $levels = Level::all();
        return Inertia::render('Admin/Units/Edit', [
            'unit' => $unit,
            'levels' => $levels
        ]);
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        return redirect()->route('units.index')->with('success', 'Unidad actualizada correctamente');
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('destroy', $unit);
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unidad eliminada correctamente');
    }
}
