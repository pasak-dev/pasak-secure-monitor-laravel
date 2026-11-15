<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxSessionController.
 */
class SessionController extends PasaxSessionController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\SessionController', false)) {
    class_alias(PasaxSessionController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\SessionController');
}
