<?php

namespace Pasak\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasakBlockedIpApiController.
 */
class BlockedIpApiController extends PasakBlockedIpApiController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Api\\BlockedIpApiController', false)) {
    class_alias(PasakBlockedIpApiController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Api\\BlockedIpApiController');
}
