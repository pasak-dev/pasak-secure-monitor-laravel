<?php

namespace Pasax\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasaxSecurityLogApiController.
 */
class SecurityLogApiController extends PasaxSecurityLogApiController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Api\\SecurityLogApiController', false)) {
    class_alias(PasaxSecurityLogApiController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Api\\SecurityLogApiController');
}
