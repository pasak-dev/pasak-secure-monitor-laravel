<?php

namespace Pasax\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasaxBlockedIpApiController.
 */
class BlockedIpApiController extends PasaxBlockedIpApiController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Api\\BlockedIpApiController', false)) {
    class_alias(PasaxBlockedIpApiController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Api\\BlockedIpApiController');
}
