<?php

namespace Pasak\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasakEnsureSecurityAdmin.
 */
class EnsureSecurityAdmin extends PasakEnsureSecurityAdmin {}

if (! class_exists('Pasak\SecureMonitor\Http\Middleware\EnsureSecurityAdmin', false)) {
    class_alias(PasakEnsureSecurityAdmin::class, 'Pasak\SecureMonitor\Http\Middleware\EnsureSecurityAdmin');
}
