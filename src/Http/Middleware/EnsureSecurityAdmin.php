<?php

namespace Pasax\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasaxEnsureSecurityAdmin.
 */
class EnsureSecurityAdmin extends PasaxEnsureSecurityAdmin {}

if (! class_exists('Pasax\SecureMonitor\Http\Middleware\EnsureSecurityAdmin', false)) {
    class_alias(PasaxEnsureSecurityAdmin::class, 'Pasax\SecureMonitor\Http\Middleware\EnsureSecurityAdmin');
}
