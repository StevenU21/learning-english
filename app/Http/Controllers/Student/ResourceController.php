<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResourceController extends Controller
{
    public function index(Unit $unit)
    {
        // Load resources for the unit
        $resources = Resource::where('unit_id', $unit->id)->get();
        return Inertia::render('Student/Units/Resources', [
            'unit' => $unit->only(['id','name','description']),
            'resources' => $resources->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'file_path' => $r->file_path,
            ])
        ]);
    }

    public function download(Resource $resource)
    {
        // Basic authorization: ensure user can access the unit (could be expanded)
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
