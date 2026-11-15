<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxUnblockIpCommand.
 */
class UnblockIpAddress extends PasaxUnblockIpCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\UnblockIpAddress', false)) {
    class_alias(PasaxUnblockIpCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\UnblockIpAddress');
}
