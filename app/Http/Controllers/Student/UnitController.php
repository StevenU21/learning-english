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
        $levelParam = $request->input('level') ?? $request->input('level_id');
        $levels = Level::all();
        $units = Unit::query()
            ->when($levelParam, function ($q) use ($levelParam) {
                if (is_numeric($levelParam)) {
                    $q->where('level_id', $levelParam);
                } else {
                    $q->whereHas('level', fn($q) => $q->where('slug', $levelParam));
                }
            })
            ->with([
                'level',
                'unitUserProgress' => fn($q) => $q->where('user_id', auth()->id()),
            ])
            ->withSum('lessons as lessons_sum_duration', 'duration')
            ->get()
            ->map(fn($unit) => [
                'id' => $unit->id,
                'slug' => $unit->slug,
                'level_id' => $unit->level_id,
                'name' => $unit->name,
                'description' => $unit->description,
                'expected_time' => (int) $unit->expected_time,
                'image_url' => $unit->image_url,
                'level' => $unit->level,
                'progress' => optional($unit->unitUserProgress->first())->progress ?? 0,
                'status' => optional($unit->unitUserProgress->first())->status ?? 'no_comenzado',
            ]);

        return Inertia::render('Student/Units/Index', [
            'levels' => $levels,
            'units' => $units,
            'selectedLevel' => $levelParam
        ]);
    }
}
