<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $stats = $dashboardService->getStats();

        return Inertia::render('Dashboard', compact('stats'));
    }
}
