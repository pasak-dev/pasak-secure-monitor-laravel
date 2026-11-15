<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxServerController.
 */
class ServerController extends PasaxServerController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\ServerController', false)) {
    class_alias(PasaxServerController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\ServerController');
}
