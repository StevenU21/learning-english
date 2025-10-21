<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Http\Resources\UnitResource;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * List units, optionally filtered by level id or slug.
     */
    public function index(Request $request)
    {
        $levelParam = $request->input('level') ?? $request->input('level_id');
        $units = Unit::query()
            ->when($levelParam, function ($q) use ($levelParam) {
                if (is_numeric($levelParam)) {
                    $q->where('level_id', $levelParam);
                } else {
                    $q->whereHas('level', fn($q) => $q->where('slug', $levelParam));
                }
            })
            ->with(['level', 'unitUserProgress' => fn($q) => $q->where('user_id', auth()->id())])
            ->get();

        return UnitResource::collection($units);
    }

    /**
     * Show a specific unit with progress.
     */
    public function show(Unit $unit)
    {
        $unit->load(['level', 'unitUserProgress' => fn($q) => $q->where('user_id', auth()->id())]);
        return new UnitResource($unit);
    }
}
