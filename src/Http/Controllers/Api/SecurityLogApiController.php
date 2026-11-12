<?php

namespace Pasak\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasakSecurityLogApiController.
 */
class SecurityLogApiController extends PasakSecurityLogApiController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Api\\SecurityLogApiController', false)) {
    class_alias(PasakSecurityLogApiController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Api\\SecurityLogApiController');
}
