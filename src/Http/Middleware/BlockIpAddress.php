<?php

namespace Pasak\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasakBlockIpAddress.
 */
class BlockIpAddress extends PasakBlockIpAddress {}

if (! class_exists('Pasak\SecureMonitor\Http\Middleware\BlockIpAddress', false)) {
    class_alias(PasakBlockIpAddress::class, 'Pasak\SecureMonitor\Http\Middleware\BlockIpAddress');
}
