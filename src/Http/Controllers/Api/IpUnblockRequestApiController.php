<?php

namespace Pasak\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasakIpUnblockRequestApiController.
 */
class IpUnblockRequestApiController extends PasakIpUnblockRequestApiController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Api\\IpUnblockRequestApiController', false)) {
    class_alias(PasakIpUnblockRequestApiController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Api\\IpUnblockRequestApiController');
}
