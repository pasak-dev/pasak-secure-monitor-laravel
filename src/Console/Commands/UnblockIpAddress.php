<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakUnblockIpCommand.
 */
class UnblockIpAddress extends PasakUnblockIpCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\UnblockIpAddress', false)) {
    class_alias(PasakUnblockIpCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\UnblockIpAddress');
}
