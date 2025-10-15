<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ResourceRequest;
use App\Models\Resource;
use App\Models\Unit;
use App\Services\FileService;
use File;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ResourceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Resource::class);
        $permissions = PermissionHelper::getPermissions('resources', ['download']);
        // Aplicar filtro por unidad si se especifica
        $query = Resource::with('unit');
        if ($request->filled('unit')) {
            $query->where('unit_id', $request->input('unit'));
        }
        $resources = $query->paginate(10)->withQueryString();

        // Lista de unidades para filtro
        $units = Unit::all();
        return Inertia::render('Admin/Resources/Index', [
            'resources' => $resources,
            'permissions' => $permissions,
            'units' => $units,
            'filters' => [
                'unit' => $request->input('unit', ''),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Resource::class);
        $units = Unit::all();
        return Inertia::render('Admin/Resources/Create', [
            'units' => $units
        ]);
    }

    public function show(Resource $resource)
    {
        $this->authorize('view', $resource);
        $resource->load('unit');
        return Inertia::render('Admin/Resources/Show', [
            'resource' => $resource
        ]);
    }

    public function store(ResourceRequest $request, FileService $fileService)
    {
        $this->authorize('create', Resource::class);
        $data = $request->validated();
        $resource = new Resource($data);
        if ($request->hasFile('file_path')) {
            $path = $fileService->storeLocal($resource, 'file_path', $request->file('file_path'), 'resources');
            $resource->file_path = $path;
            $resource->save();
        } else {
            $resource->save();
        }
    return redirect()->route('resources.index', $request->query())->with('success', 'Recurso creado correctamente');
    }

    public function edit(Resource $resource)
    {
        $this->authorize('update', $resource);
        $units = Unit::all();
        return Inertia::render('Admin/Resources/Edit', [
            'resource' => $resource,
            'units' => $units
        ]);
    }

    public function update(ResourceRequest $request, Resource $resource, FileService $fileService)
    {
        $this->authorize('update', $resource);
        $data = $request->validated();
        if ($request->hasFile('file_path')) {
            // Update file and persist returned path
            $path = $fileService->updateLocal($resource, 'file_path', $request->file('file_path'), 'resources');
            $resource->file_path = $path;
            // Update other fields too
            $resource->fill(collect($data)->except('file_path')->toArray());
            $resource->save();
        } else {
            // Si no hay archivo nuevo, actualiza los demás campos
            $resource->update(collect($data)->except('file_path')->toArray());
        }
    return redirect()->route('resources.index', $request->query())->with('success', 'Recurso actualizado correctamente');
    }

    public function destroy(Resource $resource, FileService $fileService)
    {
        $this->authorize('destroy', $resource);
        $fileService->deleteLocal($resource, 'file_path');
        $resource->delete();
    return redirect()->route('resources.index', request()->query())->with('success', 'Recurso eliminado correctamente');
    }

    public function download(Resource $resource)
    {
        $this->authorize('download', $resource);
        $path = $resource->getAttribute('file_path');
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'Archivo no encontrado');
        }
        $absolutePath = Storage::disk('public')->path($path);
        return response()->download($absolutePath, basename($path));
    }
}
