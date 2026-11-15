<?php

namespace Pasax\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasaxAllowedIpApiController.
 */
class AllowedIpApiController extends PasaxAllowedIpApiController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Api\\AllowedIpApiController', false)) {
    class_alias(PasaxAllowedIpApiController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Api\\AllowedIpApiController');
}
