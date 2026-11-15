<?php

namespace Pasax\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasaxUserSessionApiController.
 */
class UserSessionApiController extends PasaxUserSessionApiController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Api\\UserSessionApiController', false)) {
    class_alias(PasaxUserSessionApiController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Api\\UserSessionApiController');
}
