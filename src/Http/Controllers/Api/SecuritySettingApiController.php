<?php

namespace Pasax\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasaxSecuritySettingApiController.
 */
class SecuritySettingApiController extends PasaxSecuritySettingApiController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Api\\SecuritySettingApiController', false)) {
    class_alias(PasaxSecuritySettingApiController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Api\\SecuritySettingApiController');
}
