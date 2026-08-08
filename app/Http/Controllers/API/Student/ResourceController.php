<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * List resources by unit.
     */
    public function index(Request $request, $unitId)
    {
        $resources = Resource::where('unit_id', $unitId)->get();

        return ResourceResource::collection($resources);
    }

    /**
     * Download a specific resource file.
     */
    public function download(Resource $resource)
    {
        if ($resource->unit === null) {
            return response()->json(['message' => 'Recurso no válido'], 404);
        }
        $path = storage_path('app/public/'.$resource->file_path);
        if (! file_exists($path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return response()->download($path);
    }
}
