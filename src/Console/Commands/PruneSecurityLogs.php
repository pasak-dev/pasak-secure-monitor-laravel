<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxPruneSecurityLogsCommand.
 */
class PruneSecurityLogs extends PasaxPruneSecurityLogsCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\PruneSecurityLogs', false)) {
    class_alias(PasaxPruneSecurityLogsCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\PruneSecurityLogs');
}
