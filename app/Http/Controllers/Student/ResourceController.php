<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Classes\CollectionHelper;
use App\Models\Unit;
use Inertia\Inertia;

class ResourceController extends Controller
{
    public function index(Unit $unit)
    {
        $resources = Resource::where('unit_id', $unit->id)->get();
        return Inertia::render('Student/Units/Resources', [
            'unit' => $unit->only(['id', 'name', 'description']),
            'resources' => CollectionHelper::transform($resources, fn($r) => [
                ...$r->toArray(),
            ])
        ]);
    }

    public function download(Resource $resource)
    {
        if ($resource->unit === null) {
            abort(404);
        }
        $path = storage_path('app/public/' . $resource->file_path);
        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado');
        }
        return response()->download($path);
    }
}
