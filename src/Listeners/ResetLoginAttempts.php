<?php

namespace Pasax\SecureMonitor\Listeners;

/**
 * Legacy alias for PasaxResetLoginAttempts.
 */
class ResetLoginAttempts extends PasaxResetLoginAttempts {}

if (! class_exists('Pasax\SecureMonitor\Listeners\ResetLoginAttempts', false)) {
    class_alias(PasaxResetLoginAttempts::class, 'Pasax\SecureMonitor\Listeners\ResetLoginAttempts');
}
