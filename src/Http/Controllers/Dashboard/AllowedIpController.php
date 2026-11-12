<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasakAllowedIpController.
 */
class AllowedIpController extends PasakAllowedIpController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\AllowedIpController', false)) {
    class_alias(PasakAllowedIpController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\AllowedIpController');
}
