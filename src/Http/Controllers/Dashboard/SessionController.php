<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasakSessionController.
 */
class SessionController extends PasakSessionController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\SessionController', false)) {
    class_alias(PasakSessionController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\SessionController');
}
