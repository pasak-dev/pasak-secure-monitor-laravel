<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakScanAccessLogsCommand.
 */
class SecurityScanAccessLogs extends PasakScanAccessLogsCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\SecurityScanAccessLogs', false)) {
    class_alias(PasakScanAccessLogsCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\SecurityScanAccessLogs');
}
