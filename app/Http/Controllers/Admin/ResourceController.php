<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceRequest;
use App\Models\Resource;
use App\Models\Unit;
use App\Services\FileService;
use File;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class ResourceController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Resource::class);
        $permissions = PermissionHelper::getPermissions('resources', ['download']);
        $resources = Resource::with('unit')->paginate(10);
        return Inertia::render('Admin/Resources/Index', [
            'resources' => $resources,
            'permissions' => $permissions
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
        return redirect()->route('resources.index')->with('success', 'Recurso creado correctamente');
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
            $fileService->updateLocal($resource, 'file_path', $request->file('file_path'), 'resources');
        } else {
            // Si no hay archivo nuevo, actualiza los demás campos
            $resource->update(array_filter($data, function ($key) {
                return $key !== 'file_path';
            }, ARRAY_FILTER_USE_KEY));
        }
        return redirect()->route('resources.index')->with('success', 'Recurso actualizado correctamente');
    }

    public function destroy(Resource $resource, FileService $fileService)
    {
        $this->authorize('destroy', $resource);
        $fileService->deleteLocal($resource, 'file_path');
        $resource->delete();
        return redirect()->route('resources.index')->with('success', 'Recurso eliminado correctamente');
    }

    public function download(Resource $resource)
    {
        $this->authorize('download', $resource);
        return response()->download(storage_path('app/public/' . $resource->getAttribute('file_path')));
    }
}
