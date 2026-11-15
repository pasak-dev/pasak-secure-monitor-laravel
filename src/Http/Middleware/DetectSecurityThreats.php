<?php

namespace Pasax\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasaxDetectSecurityThreats.
 */
class DetectSecurityThreats extends PasaxDetectSecurityThreats {}

if (! class_exists('Pasax\SecureMonitor\Http\Middleware\DetectSecurityThreats', false)) {
    class_alias(PasaxDetectSecurityThreats::class, 'Pasax\SecureMonitor\Http\Middleware\DetectSecurityThreats');
}
