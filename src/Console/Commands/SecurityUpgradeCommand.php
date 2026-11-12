<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakUpgradeCommand.
 */
class SecurityUpgradeCommand extends PasakUpgradeCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\SecurityUpgradeCommand', false)) {
    class_alias(PasakUpgradeCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\SecurityUpgradeCommand');
}
