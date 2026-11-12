<?php

namespace Pasak\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasakSecuritySettingApiController.
 */
class SecuritySettingApiController extends PasakSecuritySettingApiController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Api\\SecuritySettingApiController', false)) {
    class_alias(PasakSecuritySettingApiController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Api\\SecuritySettingApiController');
}
