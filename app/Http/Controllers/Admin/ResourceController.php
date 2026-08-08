<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PermissionHelper;
use App\DTOs\LocalFileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceRequest;
use App\Models\Resource;
use App\Models\Unit;
use App\Services\FileService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ResourceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Resource::class);
        $permissions = PermissionHelper::getPermissions('resources', ['download']);

        $resources = Resource::with('unit')
            ->when($request->filled('unit'), fn ($q) => $q->where('unit_id', $request->input('unit')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Resources/Index', [
            'resources' => $resources,
            'permissions' => $permissions,
            'units' => Unit::all(),
            'filters' => [
                'unit' => $request->input('unit', ''),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Resource::class);

        return Inertia::render('Admin/Resources/Create', [
            'units' => Unit::all(),
        ]);
    }

    public function show(Resource $resource)
    {
        $this->authorize('view', $resource);
        $resource->load('unit');

        return Inertia::render('Admin/Resources/Show', [
            'resource' => $resource,
        ]);
    }

    public function store(ResourceRequest $request, FileService $fileService)
    {
        $this->authorize('create', Resource::class);
        $data = $request->validated();
        $resource = new Resource($data);
        if ($request->hasFile('file_path')) {
            $resource->file_path = $fileService->storeLocal(new LocalFileDTO($resource, 'file_path', $request->file('file_path'), 'resources'));
        }
        $resource->save();

        return redirect()->route('resources.index', $request->query())->with('success', 'Recurso creado correctamente');
    }

    public function edit(Resource $resource)
    {
        $this->authorize('update', $resource);

        return Inertia::render('Admin/Resources/Edit', [
            'resource' => $resource,
            'units' => Unit::all(),
        ]);
    }

    public function update(ResourceRequest $request, Resource $resource, FileService $fileService)
    {
        $this->authorize('update', $resource);
        $data = $request->validated();
        if ($request->hasFile('file_path')) {
            $resource->file_path = $fileService->updateLocal(new LocalFileDTO($resource, 'file_path', $request->file('file_path'), 'resources'));
            $resource->fill(collect($data)->except('file_path')->toArray());
            $resource->save();
        } else {
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
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download(Storage::disk('public')->path($path), basename($path));
    }
}
