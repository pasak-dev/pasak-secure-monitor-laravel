<?php

namespace Pasax\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasaxServerSecurityApiController.
 */
class ServerSecurityApiController extends PasaxServerSecurityApiController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Api\\ServerSecurityApiController', false)) {
    class_alias(PasaxServerSecurityApiController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Api\\ServerSecurityApiController');
}
