<?php

namespace Pasak\SecureMonitor\Services;

/**
 * Legacy alias for PasakLoginThrottleService.
 */
class LoginThrottleService extends PasakLoginThrottleService {}

if (! class_exists('Pasak\SecureMonitor\Services\LoginThrottleService', false)) {
    class_alias(PasakLoginThrottleService::class, 'Pasak\SecureMonitor\Services\LoginThrottleService');
}
