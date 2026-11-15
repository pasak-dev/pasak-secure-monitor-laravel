<?php

namespace Pasax\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasaxBaselineCommand.
 */
class SecurityBaselineCommand extends PasaxBaselineCommand {}

if (! class_exists('Pasax\\SecureMonitor\\Console\\Commands\\SecurityBaselineCommand', false)) {
    class_alias(PasaxBaselineCommand::class, 'Pasax\\SecureMonitor\\Console\\Commands\\SecurityBaselineCommand');
}
