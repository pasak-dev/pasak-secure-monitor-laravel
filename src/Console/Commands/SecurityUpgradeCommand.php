<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxUpgradeCommand.
 */
class SecurityUpgradeCommand extends PasaxUpgradeCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\SecurityUpgradeCommand', false)) {
    class_alias(PasaxUpgradeCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\SecurityUpgradeCommand');
}
