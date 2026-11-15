<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxOverviewController.
 */
class OverviewController extends PasaxOverviewController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\OverviewController', false)) {
    class_alias(PasaxOverviewController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\OverviewController');
}
