<?php

namespace Pasak\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasakServerSecurityApiController.
 */
class ServerSecurityApiController extends PasakServerSecurityApiController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Api\\ServerSecurityApiController', false)) {
    class_alias(PasakServerSecurityApiController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Api\\ServerSecurityApiController');
}
