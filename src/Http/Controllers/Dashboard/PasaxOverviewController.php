<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Services\PasaxService;

/**
 * Overview page for the Blade monitoring dashboard.
 */
class PasaxOverviewController extends Controller
{
    use RespondsWithPasaxDashboard;

    public function __invoke(Request $request, PasaxService $security): View
    {
        $range = (string) $request->query('range', 'week');

        if (! in_array($range, ['week', 'month', 'year'], true)) {
            $range = 'week';
        }

        return $this->dashboardResponse(
            'pages.pasax.overview',
            [
                'range' => $range,
                'stats' => $security->stats(),
                'levels' => $security->levelBreakdown(),
                'trend' => $security->attackTrend($range),
                'attackers' => $security->topAttackers(5)->all(),
                'recentLogs' => PasaxLog::query()
                    ->latest('id')
                    ->limit(6)
                    ->get(),
                'urls' => [
                    'self' => route('pasax.dashboard.overview'),
                    'logs' => route('pasax.dashboard.logs'),
                ],
            ]
        );
    }
}
