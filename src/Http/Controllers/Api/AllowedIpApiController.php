<?php

namespace Pasak\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasakAllowedIpApiController.
 */
class AllowedIpApiController extends PasakAllowedIpApiController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Api\\AllowedIpApiController', false)) {
    class_alias(PasakAllowedIpApiController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Api\\AllowedIpApiController');
}
