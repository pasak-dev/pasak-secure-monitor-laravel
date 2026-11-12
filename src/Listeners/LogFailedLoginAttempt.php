<?php

namespace Pasak\SecureMonitor\Listeners;

/**
 * Legacy alias for PasakLogFailedLoginAttempt.
 */
class LogFailedLoginAttempt extends PasakLogFailedLoginAttempt {}

if (! class_exists('Pasak\SecureMonitor\Listeners\LogFailedLoginAttempt', false)) {
    class_alias(PasakLogFailedLoginAttempt::class, 'Pasak\SecureMonitor\Listeners\LogFailedLoginAttempt');
}
