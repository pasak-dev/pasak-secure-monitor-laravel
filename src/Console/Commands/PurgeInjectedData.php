<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxPurgeInjectedDataCommand.
 */
class PurgeInjectedData extends PasaxPurgeInjectedDataCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\PurgeInjectedData', false)) {
    class_alias(PasaxPurgeInjectedDataCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\PurgeInjectedData');
}
