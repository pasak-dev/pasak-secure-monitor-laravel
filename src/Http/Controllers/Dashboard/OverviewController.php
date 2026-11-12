<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasakOverviewController.
 */
class OverviewController extends PasakOverviewController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\OverviewController', false)) {
    class_alias(PasakOverviewController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\OverviewController');
}
