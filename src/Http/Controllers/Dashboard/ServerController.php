<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasakServerController.
 */
class ServerController extends PasakServerController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\ServerController', false)) {
    class_alias(PasakServerController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\ServerController');
}
