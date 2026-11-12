<?php

namespace Pasak\SecureMonitor\Services;

/**
 * Legacy alias for PasakUserLoginService.
 */
class UserLoginService extends PasakUserLoginService {}

if (! class_exists('Pasak\SecureMonitor\Services\UserLoginService', false)) {
    class_alias(PasakUserLoginService::class, 'Pasak\SecureMonitor\Services\UserLoginService');
}
