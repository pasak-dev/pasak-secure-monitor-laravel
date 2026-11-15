<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxInstallCommand.
 */
class SecurityInstallCommand extends PasaxInstallCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\SecurityInstallCommand', false)) {
    class_alias(PasaxInstallCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\SecurityInstallCommand');
}
