<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakPurgeInjectedDataCommand.
 */
class PurgeInjectedData extends PasakPurgeInjectedDataCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\PurgeInjectedData', false)) {
    class_alias(PasakPurgeInjectedDataCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\PurgeInjectedData');
}
