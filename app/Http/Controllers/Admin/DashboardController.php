<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $stats = $dashboardService->getStats();
        return Inertia::render("Dashboard", compact('stats'));
    }
}
