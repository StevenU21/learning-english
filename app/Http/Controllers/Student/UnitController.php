<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $levelId = $request->input('level_id');
        $levels = Level::all();
        $unitsQuery = Unit::query();
        if ($levelId) {
            $unitsQuery->where('level_id', $levelId);
        }
        $units = $unitsQuery->with([
            'level',
            'unitUserProgress' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ])->get()->map(function ($unit) {
            $progress = $unit->unitUserProgress->first();
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'description' => $unit->description,
                'expected_time' => $unit->expected_time,
                'image_url' => $unit->image_url,
                'level' => $unit->level,
                'progress' => $progress ? $progress->progress : 0,
                'status' => $progress ? $progress->status : 'no_comenzado',
            ];
        });

        return Inertia::render('Student/Units/Index', [
            'levels' => $levels,
            'units' => $units,
            'selectedLevel' => $levelId
        ]);
    }
}
