<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxAllowedIpController.
 */
class AllowedIpController extends PasaxAllowedIpController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\AllowedIpController', false)) {
    class_alias(PasaxAllowedIpController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\AllowedIpController');
}
