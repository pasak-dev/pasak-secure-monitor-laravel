<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxScanAccessLogsCommand.
 */
class SecurityScanAccessLogs extends PasaxScanAccessLogsCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\SecurityScanAccessLogs', false)) {
    class_alias(PasaxScanAccessLogsCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\SecurityScanAccessLogs');
}
