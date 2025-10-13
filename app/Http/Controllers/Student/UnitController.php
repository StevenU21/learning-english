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
        // Accept either a numeric id or a slug for level filtering
        $levelParam = $request->input('level') ?? $request->input('level_id');
        $levels = Level::all();
        $unitsQuery = Unit::query();
        if ($levelParam) {
            if (is_numeric($levelParam)) {
                $unitsQuery->where('level_id', $levelParam);
            } else {
                $unitsQuery->whereHas('level', function ($q) use ($levelParam) {
                    $q->where('slug', $levelParam);
                });
            }
        }
        $units = $unitsQuery->with([
            'level',
            'unitUserProgress' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ])
        ->withSum('lessons as lessons_sum_duration', 'duration')
        ->get()->map(function ($unit) {
            $progress = $unit->unitUserProgress->first();
            return [
                'id' => $unit->id,
                'slug' => $unit->slug,
                'level_id' => $unit->level_id,
                'name' => $unit->name,
                'description' => $unit->description,
                'expected_time' => (int) $unit->expected_time,
                'image_url' => $unit->image_url,
                'level' => $unit->level,
                'progress' => $progress ? $progress->progress : 0,
                'status' => $progress ? $progress->status : 'no_comenzado',
            ];
        });

        return Inertia::render('Student/Units/Index', [
            'levels' => $levels,
            'units' => $units,
            'selectedLevel' => $levelParam
        ]);
    }
}
