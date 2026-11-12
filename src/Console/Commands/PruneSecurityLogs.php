<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakPruneSecurityLogsCommand.
 */
class PruneSecurityLogs extends PasakPruneSecurityLogsCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\PruneSecurityLogs', false)) {
    class_alias(PasakPruneSecurityLogsCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\PruneSecurityLogs');
}
