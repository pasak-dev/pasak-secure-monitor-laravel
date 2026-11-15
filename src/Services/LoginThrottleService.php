<?php

namespace Pasax\SecureMonitor\Services;

/**
 * Legacy alias for PasaxLoginThrottleService.
 */
class LoginThrottleService extends PasaxLoginThrottleService {}

if (! class_exists('Pasax\SecureMonitor\Services\LoginThrottleService', false)) {
    class_alias(PasaxLoginThrottleService::class, 'Pasax\SecureMonitor\Services\LoginThrottleService');
}
