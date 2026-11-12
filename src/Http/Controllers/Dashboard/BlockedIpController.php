<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasakBlockedIpController.
 */
class BlockedIpController extends PasakBlockedIpController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\BlockedIpController', false)) {
    class_alias(PasakBlockedIpController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\BlockedIpController');
}
