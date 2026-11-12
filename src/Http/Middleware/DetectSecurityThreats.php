<?php

namespace Pasak\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasakDetectSecurityThreats.
 */
class DetectSecurityThreats extends PasakDetectSecurityThreats {}

if (! class_exists('Pasak\SecureMonitor\Http\Middleware\DetectSecurityThreats', false)) {
    class_alias(PasakDetectSecurityThreats::class, 'Pasak\SecureMonitor\Http\Middleware\DetectSecurityThreats');
}
