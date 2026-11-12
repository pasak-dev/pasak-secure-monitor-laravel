<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakDeployCommand.
 */
class SecurityDeployCommand extends PasakDeployCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\SecurityDeployCommand', false)) {
    class_alias(PasakDeployCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\SecurityDeployCommand');
}
