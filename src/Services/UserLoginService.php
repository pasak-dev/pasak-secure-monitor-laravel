<?php

namespace Pasax\SecureMonitor\Services;

/**
 * Legacy alias for PasaxUserLoginService.
 */
class UserLoginService extends PasaxUserLoginService {}

if (! class_exists('Pasax\SecureMonitor\Services\UserLoginService', false)) {
    class_alias(PasaxUserLoginService::class, 'Pasax\SecureMonitor\Services\UserLoginService');
}
