<?php

namespace Pasak\SecureMonitor\Http\Controllers\Api;

/**
 * Legacy alias for PasakUserSessionApiController.
 */
class UserSessionApiController extends PasakUserSessionApiController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Api\\UserSessionApiController', false)) {
    class_alias(PasakUserSessionApiController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Api\\UserSessionApiController');
}
