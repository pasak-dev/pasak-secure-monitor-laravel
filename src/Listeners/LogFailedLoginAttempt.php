<?php

namespace Pasax\SecureMonitor\Listeners;

/**
 * Legacy alias for PasaxLogFailedLoginAttempt.
 */
class LogFailedLoginAttempt extends PasaxLogFailedLoginAttempt {}

if (! class_exists('Pasax\SecureMonitor\Listeners\LogFailedLoginAttempt', false)) {
    class_alias(PasaxLogFailedLoginAttempt::class, 'Pasax\SecureMonitor\Listeners\LogFailedLoginAttempt');
}
