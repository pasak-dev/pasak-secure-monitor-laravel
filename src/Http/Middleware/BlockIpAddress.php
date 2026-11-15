<?php

namespace Pasax\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasaxBlockIpAddress.
 */
class BlockIpAddress extends PasaxBlockIpAddress {}

if (! class_exists('Pasax\SecureMonitor\Http\Middleware\BlockIpAddress', false)) {
    class_alias(PasaxBlockIpAddress::class, 'Pasax\SecureMonitor\Http\Middleware\BlockIpAddress');
}
