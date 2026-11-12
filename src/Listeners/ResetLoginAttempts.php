<?php

namespace Pasak\SecureMonitor\Listeners;

/**
 * Legacy alias for PasakResetLoginAttempts.
 */
class ResetLoginAttempts extends PasakResetLoginAttempts {}

if (! class_exists('Pasak\SecureMonitor\Listeners\ResetLoginAttempts', false)) {
    class_alias(PasakResetLoginAttempts::class, 'Pasak\SecureMonitor\Listeners\ResetLoginAttempts');
}
