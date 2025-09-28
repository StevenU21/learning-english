<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Level;
use App\Models\Unit;
use App\Services\FileService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class UnitController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', Unit::class);
        $permissions = PermissionHelper::getPermissions('units');
        $units = Unit::with('level')->get();
        return Inertia::render('Admin/Units/Index', [
            'units' => $units,
            'permissions' => $permissions
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

    public function store(UnitRequest $request, FileService $fileService)
    {
        $data = $request->validated();

        $unit = new Unit();

        if ($request->hasFile('image')) {
            $data['image'] = $fileService->storeLocal($unit, 'image', $request->file('image'));
        }

        $unit->fill($data);
        $unit->save();

        return redirect()->route('units.index')->with('success', 'Unidad creada correctamente');
    }

    public function show(Unit $unit)
    {
        $this->authorize('view', $unit);
        $unit->load('level');
        return Inertia::render('Admin/Units/Show', [
            'unit' => $unit
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

    public function update(UnitRequest $request, Unit $unit, FileService $fileService)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $fileService->storeLocal($unit, 'image', $request->file('image'));
        }

        $unit->update($data);
        return redirect()->route('units.index')->with('success', 'Unidad actualizada correctamente');
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('destroy', $unit);
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unidad eliminada correctamente');
    }
}
