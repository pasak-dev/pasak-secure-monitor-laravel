<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxBlockedIpController.
 */
class BlockedIpController extends PasaxBlockedIpController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\BlockedIpController', false)) {
    class_alias(PasaxBlockedIpController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\BlockedIpController');
}
