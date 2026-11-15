<?php

namespace Pasax\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasaxIpUnblockRequestApiController.
 */
class IpUnblockRequestApiController extends PasaxIpUnblockRequestApiController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Api\\IpUnblockRequestApiController', false)) {
    class_alias(PasaxIpUnblockRequestApiController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Api\\IpUnblockRequestApiController');
}
