<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxLogController.
 */
class LogController extends PasaxLogController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\LogController', false)) {
    class_alias(PasaxLogController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\LogController');
}
