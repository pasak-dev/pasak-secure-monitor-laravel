<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakInstallCommand.
 */
class SecurityInstallCommand extends PasakInstallCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\SecurityInstallCommand', false)) {
    class_alias(PasakInstallCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\SecurityInstallCommand');
}
