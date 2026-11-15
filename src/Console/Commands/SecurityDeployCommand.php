<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxDeployCommand.
 */
class SecurityDeployCommand extends PasaxDeployCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\SecurityDeployCommand', false)) {
    class_alias(PasaxDeployCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\SecurityDeployCommand');
}
