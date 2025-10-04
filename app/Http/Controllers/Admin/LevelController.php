<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelRequest;
use App\Models\Level;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class LevelController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Level::class);
        // Paginate 10 items per page for levels
        $levels = Level::paginate(10);
        return Inertia::render('Admin/Levels/Index', [
            'levels' => $levels
        ]);
    }

    public function create()
    {
        $this->authorize('create', Level::class);
        return Inertia::render('Admin/Levels/Create');
    }

    public function store(LevelRequest $request)
    {
        Level::create($request->validated());
        return redirect()->route('levels.index')->with('success', 'Nivel creado correctamente');
    }

    public function show(Level $level)
    {
        $this->authorize('view', $level);
        return Inertia::render('Admin/Levels/Show', [
            'level' => $level
        ]);
    }

    public function edit(Level $level)
    {
        $this->authorize('update', $level);
        return Inertia::render('Admin/Levels/Edit', [
            'level' => $level
        ]);
    }

    public function update(LevelRequest $request, Level $level)
    {
        $level->update($request->validated());
        return redirect()->route('levels.index')->with('success', 'Nivel actualizado correctamente');
    }

    public function destroy(Level $level)
    {
        $this->authorize('destroy', $level);
        $level->delete();
        return redirect()->route('levels.index')->with('success', 'Nivel eliminado correctamente');
    }
}
