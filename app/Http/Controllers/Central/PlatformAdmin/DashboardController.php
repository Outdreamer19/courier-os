<?php

namespace App\Http\Controllers\Central\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Services\Platform\PlatformStatsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(PlatformStatsService $stats): Response
    {
        return Inertia::render('central/admin/Dashboard', [
            'stats' => $stats->overview(),
            'signups' => $stats->signupsByMonth(),
        ]);
    }
}
